<?php
/**
 * HIGH-PERFORMANCE SYNC SYSTEM
 * Features: Parallel API Calls (curl_multi) + Database Transactions + Memory Management
 */

// 1. PERFORMANCE SETTINGS
@set_time_limit(0);             // 0 = Infinite time (run until finished)
@ini_set('memory_limit', '1G'); // Allow more RAM for large batches
if (ob_get_level()) ob_end_clean(); // Disable buffering for real-time logs

class EmbeddingSync {
    private $pdo;
    private $ollamaUrl = "http://localhost:11434/api/embeddings";
    private $model = "nomic-embed-text";
    private $startTime;
    private $maxExecutionTime = 25000; // Safety buffer (stop before 30000)

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->startTime = time();
    }

    /**
     * Main Processing Loop
     * Processes the queue in batches to save memory and use transactions.
     */
    public function processQueue($batchSize = 10) {
        echo "Starting High-Speed Sync...<br>";
        
        while (true) {
            // Check time limit
            if ((time() - $this->startTime) > $this->maxExecutionTime) {
                echo "Time limit reached. Stopping gracefully.<br>";
                break;
            }

            // 1. Fetch a Batch of pending tasks (Only get IDs first for speed)
            $stmt = $this->pdo->prepare("SELECT * FROM embedding_queue WHERE status = 'pending' LIMIT ?");
            $stmt->execute([(int)$batchSize]);
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // If no tasks left, stop.
            if (count($tasks) === 0) {
                echo "Queue is empty. Sync Complete.<br>";
                break;
            }

            // 2. Process this batch in Parallel
            $this->processBatchParallel($tasks);

            // 3. Clear memory for next iteration
            unset($tasks);
            
            // Optional: Flush output to browser
            if (php_sapi_name() !== 'cli') {
                flush();
                ob_flush();
            }
        }
    }

    /**
     * Processes a list of tasks using Parallel CURL and Transactions
     */
    private function processBatchParallel($tasks) {
        $contexts = [];
        $map = [];

        // A. Build Contexts
        foreach ($tasks as $task) {
            $ctx = $this->buildContext($task['entity_type'], $task['entity_id']);
            if ($ctx) {
                $contexts[$task['id']] = $ctx;
                $map[$task['id']] = $task;
            } else {
                // If context is empty (deleted item), mark as completed immediately
                $this->updateStatus($task['id'], 'completed');
            }
        }

        if (empty($contexts)) return;

        // B. Get Vectors in Parallel (The "Fast" Part)
        echo " > Processing batch of " . count($contexts) . " items... ";
        $vectors = $this->getOllamaEmbeddingsMulti($contexts);
        echo "Done.<br>";

        // C. Save to DB using a Transaction (The "Safe" Part)
        try {
            $this->pdo->beginTransaction();

            foreach ($vectors as $taskId => $vector) {
                if ($vector) {
                    $task = $map[$taskId];
                    $content = $contexts[$taskId];
                    
                    // Insert Vector
                    $this->saveEmbedding($task['entity_type'], $task['entity_id'], $vector, $content);
                    
                    // Update Queue Status
                    $this->updateStatus($taskId, 'completed');
                } else {
                    $this->updateStatus($taskId, 'failed');
                }
            }

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            echo "Batch Transaction Failed: " . $e->getMessage() . "<br>";
        }
    }

    /**
     * Uses curl_multi to send multiple requests to Ollama simultaneously
     */
    private function getOllamaEmbeddingsMulti($textArray) {
        $multiHandle = curl_multi_init();
        $curlHandles = [];
        $results = [];

        // Prepare Requests
        foreach ($textArray as $id => $text) {
            $ch = curl_init($this->ollamaUrl);
            $payload = json_encode(["model" => $this->model, "prompt" => $text]);
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120); 

            curl_multi_add_handle($multiHandle, $ch);
            $curlHandles[$id] = $ch;
        }

        // Execute Parallel Requests
        $running = null;
        do {
            curl_multi_exec($multiHandle, $running);
            curl_multi_select($multiHandle); // Wait for activity
        } while ($running > 0);

        // Collect Results
        foreach ($curlHandles as $id => $ch) {
            $response = curl_multi_getcontent($ch);
            $json = json_decode($response, true);
            $results[$id] = $json['embedding'] ?? null;
            
            curl_multi_remove_handle($multiHandle, $ch);
            curl_close($ch);
        }

        curl_multi_close($multiHandle);
        return $results;
    }

    private function buildContext($type, $id) {
        switch ($type) {
            case 'product':
                // Ensure 'name_en' actually exists in categories, otherwise change to 'name'
                $stmt = $this->pdo->prepare("SELECT p.*, c.name_en as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
                $stmt->execute([$id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row ? "Product: {$row['name_en']}. Category: {$row['cat_name']}. Price: {$row['price']}. Info: {$row['description_en']}" : null;

            case 'category':
                // Ensure 'name_en' exists, otherwise change to 'name'
                $stmt = $this->pdo->prepare("SELECT name_en FROM categories WHERE id = ?");
                $stmt->execute([$id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row ? "Category: {$row['name_en']}. This section contains electronic products." : null;

            case 'order':
                // FIX: Changed 'u.name' to 'u.username' (Change 'username' to your actual column name)
                $stmt = $this->pdo->prepare("SELECT o.*, u.first_name,u.last_name as user_name FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
                $stmt->execute([$id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row ? "Order #{$row['id']} by {$row['user_name']}. Total: {$row['total_amount']}. Status: {$row['status']}." : null;

            case 'user':
                // FIX: Changed 'name' to 'username' (Change 'username' to your actual column name)
                $stmt = $this->pdo->prepare("SELECT first_name,last_name, email FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row ? "Customer Profile: Name: {$row['first_name']} {$row['last_name']}, Email: {$row['email']}." : null;

            default: return null;
        }
    }

    private function saveEmbedding($type, $id, $vector, $content) {
        $sql = "INSERT INTO entity_embeddings (entity_type, entity_id, vector_data, content_text) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE vector_data = VALUES(vector_data), content_text = VALUES(content_text)";
        $this->pdo->prepare($sql)->execute([$type, $id, json_encode($vector), $content]);
    }

    private function updateStatus($id, $status) {
        $this->pdo->prepare("UPDATE embedding_queue SET status = ? WHERE id = ?")->execute([$status, $id]);
    }
}

// --- EXECUTION ---
try {
    $db_config = [ 'host' => 'localhost', 'db' => 'electronics_showroom', 'user' => 'admin', 'pass' => 'Admin@123' ];
    $pdo = new PDO("mysql:host={$db_config['host']};dbname={$db_config['db']}", $db_config['user'], $db_config['pass']);
    
    $sync = new EmbeddingSync($pdo);
    
    // IMPORTANT:
    // 1. ProcessQueue now loops automatically. 
    // 2. The argument '10' is the BATCH SIZE (Parallel requests), not total limit.
    // 3. Keep batch size around 5-10 for Ollama CPU, 20-50 for Ollama GPU.
    $sync->processQueue(10); 

} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage();
}
?>