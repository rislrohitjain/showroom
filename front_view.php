<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ElectroShowroom | Smart AI Support</title>
	  <link rel="icon" type="image/x-icon" href="https://cdn-icons-png.flaticon.com/512/3659/3659899.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    
    <style>
        :root {
            --primary-dark: #232f3e;
            --accent-orange: #ff9900;
            --bot-bubble: #e7f4f5;
            --user-bubble: #ffffff;
        }

        body, html {
            height: 100vh; margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f2;
        }

        /* Onboarding */
        #onboarding {
            position: fixed; inset: 0; background: rgba(255,255,255,0.95);
            z-index: 2000; display: flex; align-items: center; justify-content: center;
        }

        /* App Container with Background Pattern */
        .app-container {
            height: 100vh; display: none; flex-direction: column;
            max-width: 900px; margin: 0 auto; background: #fff;
            box-shadow: 0 0 30px rgba(0,0,0,0.1);
        }

        header {
            background: var(--primary-dark); color: white;
            padding: 15px; display: flex; align-items: center; justify-content: space-between;
        }

        .status-dot { height: 10px; width: 10px; border-radius: 50%; display: inline-block; margin-right: 5px; }
        .online { background: #2ecc71; box-shadow: 0 0 5px #2ecc71; }
        .offline { background: #e74c3c; }

        #chat-flow {
            flex: 1; overflow-y: auto; padding: 20px;
            background-image: url('https://www.transparenttextures.com/patterns/cubes.png');
            background-color: #f4f6f6; display: flex; flex-direction: column; gap: 15px;
        }

        /* Messages */
        .msg {
            max-width: 80%; padding: 12px 16px; border-radius: 15px;
            font-size: 14px; position: relative; box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .bot-msg { align-self: flex-start; background: var(--bot-bubble); border-bottom-left-radius: 2px; }
        .user-msg { align-self: flex-end; background: var(--user-bubble); border-bottom-right-radius: 2px; border: 1px solid #ddd; }
        
        .preview-img { max-width: 200px; border-radius: 8px; margin-top: 10px; display: block; }

        /* Bottom Controls */
        .bottom-bar { padding: 15px; border-top: 1px solid #ddd; background: #fff; }
        .input-area { 
            display: flex; gap: 10px; align-items: center; 
            background: #f0f2f2; padding: 8px 15px; border-radius: 25px;
        }
        #userInput { border: none; background: transparent; flex: 1; outline: none; padding: 5px; }
        
        .icon-btn { 
            background: none; border: none; font-size: 1.2rem; 
            color: #565959; cursor: pointer; transition: 0.2s; 
        }
        .icon-btn:hover { color: var(--accent-orange); }
        .mic-active { color: #e74c3c !important; animation: pulse 1.5s infinite; }

        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }

        #imagePreviewContainer { 
            display: none; padding: 10px; background: #fff; 
            border-top: 1px solid #eee; position: relative;
        }
        .close-preview { position: absolute; top: 5px; right: 10px; cursor: pointer; color: red; }
    </style>
</head>
<body>

<div id="onboarding">
    <div class="auth-card p-4 shadow bg-white rounded" style="width: 350px;">
        <div class="text-center mb-4">
            <i class="bi bi-cpu-fill fs-1 text-primary"></i>
            <h4 class="fw-bold">Electro Support</h4>
        </div>
        <form id="loginForm">
            <div class="mb-3">
                <label class="small fw-bold">Full Name</label>
                <input type="text" id="userName" class="form-control" placeholder="Enter name" required>
            </div>
            <div class="mb-3">
                <label class="small fw-bold">Mobile Number</label>
                <input type="tel" id="userPhone" class="form-control" placeholder="10-digit number" pattern="[0-9]{10}" required>
            </div>
            <button type="submit" class="btn btn-warning w-100 fw-bold">Start Chat</button>
        </form>
    </div>
</div>

<div class="app-container" id="mainApp">
    <header>
        <div>
            <span id="statusIcon" class="status-dot online"></span>
            <small id="statusText">Online</small>
            <h6 class="mb-0 fw-bold">ElectroShowroom AI</h6>
        </div>
        <div class="d-flex gap-2">
            <select id="langSelect" class="form-select form-select-sm" style="width: auto;">
                <option value="en-US">English</option>
                <option value="hi-IN">हिन्दी (Hindi)</option>
            </select>
            <button class="btn btn-sm btn-light" title="Click here to chat export in PDF" onclick="exportChatPDF()"><i class="bi bi-file-pdf"></i> PDF</button>
        </div>
    </header>

    <div id="chat-flow"></div>

    <div id="imagePreviewContainer">
        <span class="close-preview" onclick="clearImage()"><i class="bi bi-x-circle-fill"></i></span>
        <img id="imgPreview" class="preview-img" src="">
    </div>

    <div class="px-3 py-1">
        <div class="typing" id="typing-indicator" style="display:none;">
            <small class="text-muted"><i class="bi bi-pencil-square"></i>Agent is typing...</small>
        </div>
    </div>

    <div class="bottom-bar">
        <div class="input-area">
            <label for="fileInput" class="icon-btn"><i class="bi bi-image"></i></label>
            <input type="file" id="fileInput" hidden accept="image/png, image/jpeg">
            
            <input type="text" id="userInput" placeholder="Type or use mic...">
            
            <button id="micBtn" class="icon-btn" onclick="toggleMic()"><i class="bi bi-mic-fill"></i></button>
            
            <button id="stopBtn" class="icon-btn text-danger" style="display:none;" onclick="stopGeneration()">
                <i class="bi bi-stop-circle-fill"></i>
            </button>
            
            <button id="sendBtn" class="icon-btn" style="color:var(--accent-orange)" onclick="handleSend()">
                <i class="bi bi-send-fill"></i>
            </button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let abortController = null;
    let synth = window.speechSynthesis;
    let recognition = null;
    let selectedImageBase64 = null;

    // --- 1. Initialization & Validation ---
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        if(this.checkValidity()) {
            $('#onboarding').fadeOut();
            $('#mainApp').css('display', 'flex');
            checkOnlineStatus();
            appendMessage('Agent', `Hello ${$('#userName').val()}! I can help you in English or Hindi. How may I assist you?`);
        }
    });

    function checkOnlineStatus() {
        setInterval(() => {
            const isOnline = navigator.onLine;
            $('#statusIcon').attr('class', 'status-dot ' + (isOnline ? 'online' : 'offline'));
            $('#statusText').text(isOnline ? 'Online' : 'Offline');
        }, 3000);
    }

    // --- 2. File Handling ---
    $('#fileInput').on('change', function(e) {
        const file = e.target.files[0];
        if (file && (file.type === "image/png" || file.type === "image/jpeg")) {
            const reader = new FileReader();
            reader.onload = function(ex) {
                selectedImageBase64 = ex.target.result;
                $('#imgPreview').attr('src', selectedImageBase64);
                $('#imagePreviewContainer').show();
            };
            reader.readAsDataURL(file);
        } else {
            alert("Please upload only PNG or JPG images.");
        }
    });

    function clearImage() {
        selectedImageBase64 = null;
        $('#fileInput').val('');
        $('#imagePreviewContainer').hide();
    }

    // --- 3. Message Handling ---
    async function handleSend() {
        const text = $('#userInput').val().trim();
        if (!text && !selectedImageBase64) return;

        // User Message UI
        const userMsgId = appendMessage('user', text);
        if(selectedImageBase64) {
            $(`#${userMsgId}`).prepend(`<img src="${selectedImageBase64}" class="preview-img mb-2">`);
        }

        const prompt = text || "Check this image";
        $('#userInput').val('');
        clearImage();
        
        // Prepare Bot
        $('#typing-indicator').show();
        $('#sendBtn').hide();
        $('#stopBtn').show();
        const botId = appendMessage('bot', '');
        const botDiv = document.getElementById(botId);

        abortController = new AbortController();
        let fullText = "";

        try {
            const response = await fetch('with_stream_api_chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ 'message': prompt }),
                signal: abortController.signal
            });

            const reader = response.body.getReader();
            const decoder = new TextDecoder();

            while (true) {
                const { value, done } = await reader.read();
                if (done) break;
                const chunk = decoder.decode(value);
                const lines = chunk.split('\n');
                lines.forEach(line => {
                    if (line.startsWith('data: ')) {
                        const data = JSON.parse(line.replace('data: ', ''));
                        if (data.text) {
                            fullText += data.text;
                            botDiv.innerText = fullText;
                            $('#chat-flow').scrollTop($('#chat-flow')[0].scrollHeight);
                            $('#typing-indicator').hide();
                        }
                    }
                });
            }
        } catch (e) {
            if(e.name === 'AbortError') botDiv.innerHTML += " <i>(Stopped)</i>";
        } finally {
            $('#sendBtn').show(); $('#stopBtn').hide();
            $('#typing-indicator').hide();
        }
    }

    function appendMessage(sender, text) {
        const id = 'msg-' + Math.random().toString(36).substr(2, 9);
        const html = `
            <div class="msg ${sender}-msg" data-sender="${sender}">
                <div id="${id}" class="content">${text}</div>
                <div class="mt-2" style="font-size:10px; opacity:0.6;">
                    <span class="me-2 pointer" onclick="copyText('${id}')">COPY</span>
                    ${sender==='bot' ? `<span class="pointer" onclick="listen('${id}')">LISTEN</span>` : ''}
                </div>
            </div>`;
        $('#chat-flow').append(html);
        $('#chat-flow').scrollTop($('#chat-flow')[0].scrollHeight);
        return id;
    }

    function stopGeneration() { if(abortController) abortController.abort(); }

    // --- 4. Voice & Speech ---
    function toggleMic() {
        if (!('webkitSpeechRecognition' in window)) return alert("Voice not supported in this browser.");
        
        if (recognition) { recognition.stop(); recognition = null; return; }

        recognition = new webkitSpeechRecognition();
        recognition.lang = $('#langSelect').val();
        recognition.onstart = () => $('#micBtn').addClass('mic-active');
        recognition.onend = () => { $('#micBtn').removeClass('mic-active'); recognition = null; };
        recognition.onresult = (event) => {
            $('#userInput').val(event.results[0][0].transcript);
            handleSend();
        };
        recognition.start();
    }

    function listen(id) {
        if (synth.speaking) synth.cancel();
        const utter = new SpeechSynthesisUtterance(document.getElementById(id).innerText);
        utter.lang = $('#langSelect').val(); // Adjust voice to English or Hindi
        synth.speak(utter);
    }

    function copyText(id) {
        navigator.clipboard.writeText(document.getElementById(id).innerText);
        alert("Copied!");
    }

    function exportChatPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        let y = 20;
        $('.msg').each(function() {
            const sender = $(this).data('sender').toUpperCase();
            const text = $(this).find('.content').text();
            doc.text(`${sender}: ${text.substring(0, 50)}...`, 10, y);
            y += 10;
        });
        doc.save("Chat_Export.pdf");
    }
</script>

</body>
</html>