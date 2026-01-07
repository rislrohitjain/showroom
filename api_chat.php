<?php
// api_chat.php
header("Content-Type: application/json");
require 'db.php'; // Ensure this returns $pdo

// 1. Get Input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['message'])) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$user_id = $input['user_id'] ?? null;
$message = $input['message'];
$language = $input['language'] ?? 'en';

/**
 * STEP 2: CONVERT USER QUERY TO VECTOR (Ollama)
 */
function getQueryEmbedding($text) {
    $url = "http://localhost:11434/api/embeddings";
    $data = ["model" => "nomic-embed-text", "prompt" => $text];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $json = json_decode($response, true);
    return $json['embedding'] ?? null;
}

$queryVector = getQueryEmbedding($message);

/**
 * STEP 3: SEARCH DATABASE USING COSINE SIMILARITY
 */
$context_data = "";

if ($queryVector) {
    // We fetch all relevant embeddings (or filter by user_id if needed)
    // For large datasets, you would add a WHERE clause here
    $stmt = $pdo->prepare("SELECT entity_type, entity_id, vector_data, content_text FROM entity_embeddings");
    $stmt->execute();
    $embeddings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $matches = [];
    foreach ($embeddings as $row) {
        $dbVector = json_decode($row['vector_data'], true);
        $similarity = cosineSimilarity($queryVector, $dbVector);
        
        // Only keep highly relevant matches
        if ($similarity > 0.7) { 
            $matches[] = [
                'similarity' => $similarity,
                'content' => $row['content_text']
            ];
        }
    }

    // Sort by similarity and take top 3
    usort($matches, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
    $topMatches = array_slice($matches, 0, 3);

    foreach ($topMatches as $m) {
        $context_data .= $m['content'] . "\n";
    }
}

/**
 * STEP 4: COSINE SIMILARITY CALCULATION (Manual for MySQL 8)
 */
function cosineSimilarity($vec1, $vec2) {
    $dotProduct = 0;
    $normA = 0;
    $normB = 0;
    foreach ($vec1 as $i => $val) {
        $dotProduct += $val * $vec2[$i];
        $normA += $val * $val;
        $normB += $vec2[$i] * $vec2[$i];
    }
    return $dotProduct / (sqrt($normA) * sqrt($normB));
}

/**
 * STEP 5: PREPARE PROMPT & CALL LLM
 */
$system_prompt = "You are an agent for 'ElectroShowroom'. Use this DATABASE CONTEXT to answer. 
If context is empty, say you don't know. Language: $language.";

$final_prompt = "Context:\n$context_data\n\nUser: $message\nAgent:";

$ollama_gen_url = "http://localhost:11434/api/generate";
$gen_data = [
    "model" => "llama3.2:1b",
    "prompt" => $final_prompt,
    "stream" => false
];

$ch = curl_init($ollama_gen_url);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($gen_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$ai_res = json_decode(curl_exec($ch), true);
curl_close($ch);

echo json_encode([
    'status' => 'success',
    'reply' => $ai_res['response'] ?? 'Sorry, I am having trouble connecting.',
    'debug_context' => $context_data // Optional: see what the AI saw
]);