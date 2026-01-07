<?php
// api_chat.php
header("Content-Type: application/json");
// Disable output buffering to ensure JSON is clean
if (ob_get_level()) ob_end_clean();

require 'db.php'; // Ensure $pdo is connected

// 1. Secure Input Handling
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input JSON']);
    exit;
}

$user_id = $input['user_id'] ?? 0; // Default to 0 if guest
$message = $input['message'];
$lang    = $input['language'] ?? 'en'; // 'en' or 'hi'

// 2. Generate Vector for User Query (Nomic-Embed-Text)
function getQueryEmbedding($text) {
    $ch = curl_init("http://localhost:11434/api/embeddings");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        "model" => "nomic-embed-text",
        "prompt" => $text
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Fast timeout for vectors
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        return null; // Fail gracefully
    }
    curl_close($ch);
    
    $json = json_decode($response, true);
    return $json['embedding'] ?? null;
}

$queryVector = getQueryEmbedding($message);

// 3. Optimized Database Fetch (The "Fast" Part)
$context_data = "";

if ($queryVector) {
    /**
     * OPTIMIZATION STRATEGY:
     * 1. Public Data: Fetch Products & Categories (Limit 500 to prevent crash)
     * 2. Private Data: Fetch ONLY this specific User's Orders/Profile
     * This prevents searching thousands of other people's orders.
     */
    
    // Prepare SQL to fetch Public Items + User Specific Items
    // We use a UNION to combine them efficiently
    $sql = "
        -- 1. Public Products & Categories
        SELECT entity_type, entity_id, vector_data, content_text 
        FROM entity_embeddings 
        WHERE entity_type IN ('product', 'category')
        
        UNION ALL
        
        -- 2. Private User Data (Orders) - JOIN to verify ownership
        SELECT e.entity_type, e.entity_id, e.vector_data, e.content_text 
        FROM entity_embeddings e
        JOIN orders o ON e.entity_id = o.id
        WHERE e.entity_type = 'order' AND o.user_id = :uid1
        
        UNION ALL

        -- 3. Private User Data (Profile)
        SELECT e.entity_type, e.entity_id, e.vector_data, e.content_text 
        FROM entity_embeddings e
        WHERE e.entity_type = 'user' AND e.entity_id = :uid2
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['uid1' => $user_id, 'uid2' => $user_id]);

    // 4. Calculate Cosine Similarity in PHP
    $matches = [];
    
    // Define Cosine Function (Inline for speed inside loop logic)
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $dbVector = json_decode($row['vector_data'], true);
        
        // Skip if vector is corrupted
        if (!$dbVector) continue;

        // --- Fast Math Start ---
        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;
        
        // Loop unrolling isn't easy in PHP, but simple foreach is fast enough for <1000 items
        foreach ($queryVector as $i => $val) {
            $dbVal = $dbVector[$i] ?? 0;
            $dot += $val * $dbVal;
            $normA += $val * $val;
            $normB += $dbVal * $dbVal;
        }
        
        $similarity = ($normA * $normB) > 0 ? $dot / (sqrt($normA) * sqrt($normB)) : 0;
        // --- Fast Math End ---

        // Threshold: 0.65 is usually a good balance for Nomic
        if ($similarity > 0.65) {
            $matches[] = [
                'score' => $similarity,
                'text'  => $row['content_text']
            ];
        }
    }

    // Sort: Highest similarity first
    usort($matches, function($a, $b) {
        return $b['score'] <=> $a['score'];
    });

    // Take top 3 most relevant pieces of info
    $top_results = array_slice($matches, 0, 3);
    foreach ($top_results as $res) {
        $context_data .= "- " . $res['text'] . "\n";
    }
}

// 5. Build AI Prompt (With Hindi Support)
$sys_instruction = "You are a helpful support agent for 'ElectroShowroom'. 
Use the provided DATA CONTEXT to answer the user's question accurately.
If the answer is not in the context, politely say you don't know.";

if ($lang === 'hi') {
    $sys_instruction .= " IMPORTANT: The user is asking in Hindi. You MUST reply in Hindi (Devanagari script). Translate the context facts into natural Hindi.";
} else {
    $sys_instruction .= " Reply in English.";
}

$final_prompt = "SYSTEM INSTRUCTION: $sys_instruction\n\nDATA CONTEXT:\n$context_data\n\nUSER QUESTION: $message\nANSWER:";

// 6. Call LLM Generation
$ch = curl_init("http://localhost:11434/api/generate");
$postData = [
    "model"  => "llama3.2:1b", // Lightweight model for speed
    "prompt" => $final_prompt,
    "stream" => false,
    "options" => [
        "temperature" => 0.3 // Low temp for factual answers
    ]
];

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    echo json_encode(['error' => 'AI Service Unavailable', 'details' => $err]);
    exit;
}

$ai_data = json_decode($response, true);

// 7. Final Output
echo json_encode([
    'status' => 'success',
    'reply'  => $ai_data['response'] ?? '...',
    // 'debug_context' => $context_data // Uncomment for debugging
]);
?>