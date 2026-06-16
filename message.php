<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Chat Widget</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Floating chat button */
        #chat-widget {
            position: fixed;
            bottom: 20px;
            right: 30px;
            width: 65px;
            height: 65px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            cursor: pointer;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            z-index: 1000;
            transition: all 0.3s ease;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            }
            50% {
                box-shadow: 0 8px 35px rgba(102, 126, 234, 0.6);
            }
            100% {
                box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            }
        }

        #chat-widget:hover {
            transform: scale(1.1);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.6);
        }

        #chat-widget i {
            font-size: 24px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-5px);
            }
            60% {
                transform: translateY(-3px);
            }
        }

        /* Notification badge */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Chat window */
        #chat-window {
            position: fixed;
            bottom: 10px;
            right: 30px;
            width: 380px;
            height: 600px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            display: none;
            flex-direction: column;
            z-index: 1000;
            overflow: hidden;
            animation: slideUp 0.4s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Header */
        #chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }

        .header-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .header-text h3 {
            font-size: 16px;
            margin-bottom: 2px;
        }

        .header-text p {
            font-size: 12px;
            opacity: 0.9;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background: #2ecc71;
            border-radius: 50%;
            animation: statusPulse 2s infinite;
        }

        @keyframes statusPulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .header-actions button {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 18px;
            padding: 5px;
            border-radius: 50%;
            transition: background 0.3s ease;
        }

        .header-actions button:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Messages area */
        #chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        /* Custom scrollbar */
        #chat-messages::-webkit-scrollbar {
            width: 6px;
        }

        #chat-messages::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        #chat-messages::-webkit-scrollbar-thumb {
            background: #ddd;
            border-radius: 3px;
        }

        #chat-messages::-webkit-scrollbar-thumb:hover {
            background: #ccc;
        }

        /* Message containers */
        .message {
            display: flex;
            gap: 10px;
            animation: messageSlide 0.3s ease;
        }

        @keyframes messageSlide {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.user {
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            flex-shrink: 0;
        }

        .message.user .message-avatar {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .message-content {
            max-width: 70%;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .message-bubble {
            background: white;
            padding: 12px 16px;
            border-radius: 18px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            word-wrap: break-word;
        }

        .message.user .message-bubble {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .message-time {
            font-size: 11px;
            color: #666;
            padding: 0 5px;
        }

        .message.user .message-time {
            text-align: right;
        }

        /* Typing indicator */
        .typing-indicator {
            display: none;
            align-items: center;
            gap: 10px;
            padding: 15px;
        }

        .typing-indicator.show {
            display: flex;
        }

        .typing-dots {
            display: flex;
            gap: 4px;
        }

        .typing-dot {
            width: 8px;
            height: 8px;
            background: #999;
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }

        .typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typing {
            0%, 60%, 100% {
                transform: translateY(0);
            }
            30% {
                transform: translateY(-10px);
            }
        }

        /* Form area */
        #chat-form {
            padding: 20px;
            background: white;
            border-top: 1px solid #e9ecef;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .input-container {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        .input-wrapper {
            flex: 1;
            position: relative;
        }

        #message-input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            resize: none;
            font-family: inherit;
            font-size: 14px;
            transition: border-color 0.3s ease;
            min-height: 45px;
            max-height: 120px;
        }

        #message-input:focus {
            outline: none;
            border-color: #667eea;
        }

        .input-actions {
            position: absolute;
            right: 10px;
            bottom: 10px;
            display: flex;
            gap: 5px;
        }

        .input-actions button {
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 16px;
            padding: 5px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .input-actions button:hover {
            color: #667eea;
            background: #f0f0f0;
        }

        #send-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        #send-button:hover:not(:disabled) {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        #send-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Quick reply shortcuts */
        .chat-quick-replies {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 0 20px 12px;
            background: white;
        }

        .chat-quick-replies button {
            border: 1px solid #e0e0e0;
            background: #f8f9ff;
            color: #5a67d8;
            font-size: 12px;
            padding: 8px 12px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .chat-quick-replies button:hover {
            border-color: #667eea;
            background: #eef2ff;
        }

        /* Contact form in chat */
        .contact-form {
            background: white;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin: 10px 0;
        }

        .contact-form h4 {
            color: #333;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .form-group {
            margin-bottom: 12px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-size: 13px;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .submit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        /* Success message */
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 10px 0;
        }

        /* Error message */
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 10px 0;
        }

        /* Responsive design */
        @media (max-width: 480px) {
            #chat-window {
                width: calc(100% - 20px);
                right: 10px;
                left: 10px;
                height: 500px;
                bottom: 70px;
            }

            #chat-widget {
                bottom: 15px;
                right: 20px;
                width: 55px;
                height: 55px;
            }

            .message-content {
                max-width: 80%;
            }
        }
    </style>
</head>
<body>
    <!-- Floating chat button -->
    <div id="chat-widget">
        <i class="fas fa-comments"></i>
        <span class="notification-badge">1</span>
    </div>

    <!-- Chat window -->
    <div id="chat-window">
        <!-- Header -->
        <div id="chat-header">
            <div class="header-info">
                <div class="header-avatar">
                    <i class="fas fa-headset"></i>
                </div>
                <div class="header-text">
                    <h3>Travel Support</h3>
                    <p><span class="status-dot"></span> Online - We typically reply instantly</p>
                </div>
            </div>
            <div class="header-actions">
                <button id="minimize-chat" title="Minimize">
                    <i class="fas fa-minus"></i>
                </button>
                <button id="close-chat" title="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Messages area -->
        <div id="chat-messages">
            <!-- Welcome message -->
            <div class="message">
                <div class="message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    <div class="message-bubble">
                        👋 Welcome to Travel Support! I'm here to help you with your travel needs. How can I assist you today?
                    </div>
                    <div class="message-time">Just now</div>
                </div>
            </div>

            <!-- Typing indicator -->
            <div class="typing-indicator" id="typing-indicator">
                <div class="message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="typing-dots">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            </div>
        </div>

        <!-- Form area -->
        <div id="chat-form">
            <div class="chat-quick-replies" id="chat-quick-replies" aria-label="Quick replies">
                <button type="button" data-quick="What is the cheapest package?">Cheapest trip</button>
                <button type="button" data-quick="Show me the most expensive luxury package">Luxury trip</button>
                <button type="button" data-quick="Recommend a package for me">Recommend</button>
                <button type="button" data-quick="I want to speak to a human">Talk to human</button>
            </div>
            <div class="input-container">
                <div class="input-wrapper">
                    <textarea id="message-input" placeholder="Type your message..." rows="1"></textarea>
                    <div class="input-actions">
                        <button title="Attach file">
                            <i class="fas fa-paperclip"></i>
                        </button>
                        <button title="Emoji">
                            <i class="fas fa-smile"></i>
                        </button>
                    </div>
                </div>
                <button id="send-button" title="Send message">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatWidget = document.getElementById('chat-widget');
            const chatWindow = document.getElementById('chat-window');
            const closeButton = document.getElementById('close-chat');
            const minimizeButton = document.getElementById('minimize-chat');
            const messageInput = document.getElementById('message-input');
            const sendButton = document.getElementById('send-button');
            const chatMessages = document.getElementById('chat-messages');
            const typingIndicator = document.getElementById('typing-indicator');

            // State management
            let isTyping = false;
            let hasShownContactForm = false;
            // Hybrid chatbot API (absolute URL to ensure it works from any page)
            const CHATBOT_URL = (typeof window.CHATBOT_API === 'string' && window.CHATBOT_API)
                ? window.CHATBOT_API
                : '/12/Travel-website-Design-main/chatbot/chat.php';
            const CHATBOT_TYPEWRITER = (typeof window.CHATBOT_TYPEWRITER === 'boolean')
                ? window.CHATBOT_TYPEWRITER
                : true;
            const CHATBOT_TYPEWRITER_MIN_LEN = 48;

            document.querySelectorAll('#chat-quick-replies [data-quick]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const q = this.getAttribute('data-quick') || '';
                    if (q) {
                        sendChatMessage(q);
                    }
                });
            });

            // Toggle chat window
            chatWidget.addEventListener('click', function() {
                chatWindow.style.display = 'flex';
                chatWidget.style.display = 'none';
                messageInput.focus();
                // Remove notification badge
                const badge = chatWidget.querySelector('.notification-badge');
                if (badge) badge.remove();
            });

            closeButton.addEventListener('click', function() {
                chatWindow.style.display = 'none';
                chatWidget.style.display = 'flex';
            });

            minimizeButton.addEventListener('click', function() {
                chatWindow.style.display = 'none';
                chatWidget.style.display = 'flex';
            });

            // Auto-resize textarea
            messageInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            });

            function sendChatMessage(optionalText) {
                const message = (optionalText != null ? String(optionalText) : messageInput.value).trim();
                if (!message) {
                    return;
                }
                addUserMessage(message);
                messageInput.value = '';
                messageInput.style.height = 'auto';

                showTypingIndicator();
                sendButton.disabled = true;

                fetch(CHATBOT_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ message: message })
                })
                .then(res => res.json())
                .then(data => {
                    hideTypingIndicator();
                    sendButton.disabled = !messageInput.value.trim();
                    const reply = (data && typeof data.reply === 'string') ? data.reply : 'Sorry, I could not read the response. Please try again.';
                    const wantContact = (data && data.show_contact_form === true)
                        || /\b(human|agent|person|call\s*me|contact\s*form|talk\s+to\s+human|representative)\b/i.test(message);
                    if (CHATBOT_TYPEWRITER && reply.length >= CHATBOT_TYPEWRITER_MIN_LEN) {
                        showBotResponseTypewriter(reply, function() {
                            maybeShowContactForm(wantContact);
                        });
                    } else {
                        showBotResponse(escapeHtml(reply).replace(/\n/g, '<br>'));
                        maybeShowContactForm(wantContact);
                    }
                })
                .catch(() => {
                    hideTypingIndicator();
                    sendButton.disabled = !messageInput.value.trim();
                    showBotResponse(escapeHtml('Sorry, the assistant is unreachable right now. Please try again or use the contact option.').replace(/\n/g, '<br>'));
                });
            }

            function maybeShowContactForm(wantContact) {
                if (!hasShownContactForm && wantContact) {
                    setTimeout(() => {
                        showContactForm();
                        hasShownContactForm = true;
                    }, 400);
                }
            }

            function sendMessage() {
                sendChatMessage(null);
            }

            /** Optional typewriter reveal for longer bot replies (plain text → escaped). */
            function showBotResponseTypewriter(plainText, onDone) {
                const bubbleId = 'bot-b-' + Date.now();
                const shell = `
                    <div class="message">
                        <div class="message-avatar">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="message-content">
                            <div class="message-bubble" id="${bubbleId}"></div>
                            <div class="message-time">${getCurrentTime()}</div>
                        </div>
                    </div>
                `;
                chatMessages.insertAdjacentHTML('beforeend', shell);
                const el = document.getElementById(bubbleId);
                let i = 0;
                const total = plainText.length;
                const msPerChar = Math.max(8, Math.min(22, Math.floor(1800 / total)));
                function tick() {
                    if (!el) {
                        if (typeof onDone === 'function') {
                            onDone();
                        }
                        return;
                    }
                    if (i >= total) {
                        el.innerHTML = escapeHtml(plainText).replace(/\n/g, '<br>');
                        scrollToBottom();
                        if (typeof onDone === 'function') {
                            onDone();
                        }
                        return;
                    }
                    i = Math.min(total, i + 2);
                    el.innerHTML = escapeHtml(plainText.slice(0, i)).replace(/\n/g, '<br>');
                    scrollToBottom();
                    setTimeout(tick, msPerChar);
                }
                tick();
            }

            // Add user message
            function addUserMessage(message) {
                const messageHtml = `
                    <div class="message user">
                        <div class="message-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="message-content">
                            <div class="message-bubble">${escapeHtml(message)}</div>
                            <div class="message-time">${getCurrentTime()}</div>
                        </div>
                    </div>
                `;
                chatMessages.insertAdjacentHTML('beforeend', messageHtml);
                scrollToBottom();
            }

            // Add bot message (pass escaped HTML, or plain text — use showBotResponseText for plain)
            function showBotResponse(messageHtml) {
                const messageHtmlBlock = `
                    <div class="message">
                        <div class="message-avatar">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="message-content">
                            <div class="message-bubble">${messageHtml}</div>
                            <div class="message-time">${getCurrentTime()}</div>
                        </div>
                    </div>
                `;
                chatMessages.insertAdjacentHTML('beforeend', messageHtmlBlock);
                scrollToBottom();
            }

            // Show contact form
            function showContactForm() {
                const formHtml = `
                    <div class="message">
                        <div class="message-avatar">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="message-content">
                            <div class="contact-form">
                                <h4>📝 Leave us a message</h4>
                                <form id="contact-form">
                                    <div class="form-group">
                                        <label for="contact-name">Name *</label>
                                        <input type="text" id="contact-name" name="name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="contact-email">Email *</label>
                                        <input type="email" id="contact-email" name="email" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="contact-phone">Phone Number *</label>
                                        <input type="tel" id="contact-phone" name="phone" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="contact-message">Message *</label>
                                        <textarea id="contact-message" name="message" required></textarea>
                                    </div>
                                    <button type="submit" class="submit-btn">
                                        <i class="fas fa-paper-plane"></i> Send Message
                                    </button>
                                </form>
                            </div>
                            <div class="message-time">${getCurrentTime()}</div>
                        </div>
                    </div>
                `;
                chatMessages.insertAdjacentHTML('beforeend', formHtml);
                scrollToBottom();
                
                // Handle form submission
                document.getElementById('contact-form').addEventListener('submit', handleContactForm);
            }

            // Handle contact form submission
            function handleContactForm(e) {
                e.preventDefault();
                const formData = new FormData(e.target);
                
                // Show loading state
                const submitBtn = e.target.querySelector('.submit-btn');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
                submitBtn.disabled = true;

                fetch('submit_message.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const formContainer = e.target.closest('.contact-form');
                    if (data.status === 'success') {
                        formContainer.innerHTML = `
                            <div class="success-message">
                                <i class="fas fa-check-circle"></i> 
                                Thank you for your message! We'll get back to you within minutes.
                            </div>
                        `;
                        showBotResponse(escapeHtml("Perfect! Your message has been received. Our team will contact you shortly. Have a wonderful day! 🌟"));
                    } else {
                        formContainer.innerHTML = `
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i> 
                                Error: ${escapeHtml(String(data.message != null ? data.message : 'Unknown error'))}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const formContainer = e.target.closest('.contact-form');
                    formContainer.innerHTML = `
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i> 
                            There was an error submitting your message. Please try again.
                        </div>
                    `;
                });
            }

            // Typing indicator functions
            function showTypingIndicator() {
                typingIndicator.classList.add('show');
                scrollToBottom();
            }

            function hideTypingIndicator() {
                typingIndicator.classList.remove('show');
            }

            // Utility functions
            function scrollToBottom() {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            function getCurrentTime() {
                const now = new Date();
                return now.toLocaleTimeString('en-US', { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // Event listeners
            sendButton.addEventListener('click', sendMessage);

            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            // Enable/disable send button based on input
            messageInput.addEventListener('input', function() {
                sendButton.disabled = !this.value.trim();
            });
        });
    </script>
</body>
</html>
