<?php
// api.php

require 'db.php'; // 1. Connect to Database
// echo "ddd";die;
// 1. Disable buffering so data is sent immediately
if (ob_get_level()) ob_end_clean();
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// 2. Prepare Context (Mock data for example)
$context_data = "ElectroShowroom sells high-end electronics. 
You are a professional customer support agent for Webghzyt. 
If a user asks for contact information, use these details:
- Support Email: support@rohitjain.com
- Phone Number: +91 8946919241
- Website: www.rohitjain.com
Never mention your AI nature unless specifically asked.
";
$message = $_POST['message'] ?? 'Hello';

// 3. Setup Ollama Request
$final_prompt = "Context: $context_data\n\nUser: $message\nAgent:";
$ollama_gen_url = "http://localhost:11434/api/generate";

$gen_data = [
    "model" => "llama3.2:1b",
    "prompt" => $final_prompt,
    "stream" => true // Enable Streaming
];

$ch = curl_init($ollama_gen_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($gen_data));

// 4. The Magic: Callback function handles each chunk as it arrives
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $data) {
    // Ollama sends chunks like: {"model":..., "created_at":..., "response":" The", "done": false}
    
    // Sometimes multiple JSON objects come in one packet, split them
    $json_objects = explode("\n", trim($data));
    
    foreach ($json_objects as $json_str) {
        $json = json_decode($json_str, true);
        
        if (isset($json['response'])) {
            // Prepare data for the browser
            // We use base64 or json_encode ensures newlines don't break the SSE format
            $payload = json_encode(['text' => $json['response']]);
            
            // Send SSE formatted data
            echo "data: $payload\n\n";
            
            // FLUSH buffer immediately to client
            flush();
        }
    }
    return strlen($data);
});

curl_exec($ch);
curl_close($ch);
exit; // Stop script
?>