@extends('client.layouts.app')

@section('title', 'AI Assistant')

@section('content')
<div class="chat-container">
    {{-- Header --}}
    <div class="chat-header">
        <div class="header-main">
            <div class="ai-avatar">
                <span class="ai-avatar-pulse"></span>
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 002.25-2.25V6.75a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.75v10.5a2.25 2.25 0 002.25 2.25zM9 9h.008v.008H9V9zm0 3h.008v.008H9V12zm0 3h.008v.008H9V15zm3-6h.008v.008H12V9zm0 3h.008v.008H12V12zm0 3h.008v.008H12V15zm3-6h.008v.008H15V9zm0 3h.008v.008H15V12zm0 3h.008v.008H15V15z"/>
                </svg>
            </div>
            <div>
                <h1 class="chat-title">SA'EE AI Assistant</h1>
                <p class="chat-subtitle">Your personal logistics assistant</p>
            </div>
        </div>
        <form method="POST" action="{{ route('client.ai-chat.reset') }}">
            @csrf
            <button type="submit" class="btn-reset" title="Start a fresh chat session. Previous logs are preserved for admins.">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: -2px; margin-right: 5px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                </svg>
                New Chat
            </button>
        </form>
    </div>

    {{-- Chat Body --}}
    <div class="chat-messages" id="chatMessages">
        {{-- Welcome message --}}
        <div class="msg bot-msg">
            <div class="msg-bubble">
                <p>Hello! I am your **SA'EE AI Assistant**. I can help you track your orders, answer FAQ questions, or explain our logistical service coverages. How can I help you today?</p>
            </div>
        </div>
    </div>

    {{-- Suggestions --}}
    <div class="chat-suggestions">
        <button type="button" class="suggestion-chip" onclick="applySuggestion('Track my order')">🔍 Track my order</button>
        <button type="button" class="suggestion-chip" onclick="applySuggestion('What is SA\'EE delivery coverage?')">📍 Service coverage</button>
        <button type="button" class="suggestion-chip" onclick="applySuggestion('How can I contact support?')">📞 Contact support</button>
        <button type="button" class="suggestion-chip" onclick="applySuggestion('What are the payment methods?')">💳 Payment methods</button>
    </div>

    {{-- Input Bar --}}
    <div class="chat-input-bar">
        <form id="chatForm" onsubmit="sendMessage(event)" style="display: flex; width: 100%; gap: 12px; align-items: center;">
            <input type="text" id="userInput" placeholder="Ask a question or track an order..." required autocomplete="off">
            <button type="submit" class="btn-send">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                </svg>
            </button>
        </form>
    </div>
</div>

<style>
    /* Tech modern dashboard container */
    .chat-container {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 120px);
        background: rgba(18, 22, 45, 0.45);
        border: 1px solid rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(12px);
        border-radius: 20px;
        overflow: hidden;
        font-family: 'Inter', sans-serif;
    }

    .chat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 24px;
        background: rgba(10, 12, 28, 0.6);
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    }

    .header-main {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .ai-avatar {
        position: relative;
        width: 42px;
        height: 42px;
        background: linear-gradient(135deg, #ef4444, #b91c1c);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.35);
    }

    .ai-avatar-pulse {
        position: absolute;
        top: -3px; left: -3px; right: -3px; bottom: -3px;
        border-radius: 12px;
        border: 1.5px solid rgba(239, 68, 68, 0.4);
        animation: avatar-pulse 2s infinite;
    }

    @keyframes avatar-pulse {
        0% { transform: scale(0.97); opacity: 0.8; }
        100% { transform: scale(1.1); opacity: 0; }
    }

    .chat-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #ffffff;
        margin: 0;
        letter-spacing: -0.01em;
    }

    .chat-subtitle {
        font-size: 0.76rem;
        color: var(--text-dim, rgba(255, 255, 255, 0.45));
        margin: 2px 0 0 0;
    }

    .btn-reset {
        background: rgba(239, 68, 68, 0.09);
        border: 1px solid rgba(239, 68, 68, 0.2);
        color: #fca5a5;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.8rem;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-reset:hover {
        background: rgba(239, 68, 68, 0.2);
        border-color: rgba(239, 68, 68, 0.45);
        color: #ffffff;
    }

    /* Messages area */
    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .msg {
        display: flex;
        max-width: 80%;
        animation: msg-slide 0.25s cubic-bezier(0.4, 0, 0.2, 1) both;
    }

    .bot-msg {
        align-self: flex-start;
    }

    .user-msg {
        align-self: flex-end;
    }

    .msg-bubble {
        padding: 12px 18px;
        border-radius: 16px;
        font-size: 0.88rem;
        line-height: 1.5;
    }

    .bot-msg .msg-bubble {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.06);
        color: #e2e8f0;
        border-bottom-left-radius: 4px;
    }

    .user-msg .msg-bubble {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #ffffff;
        border-bottom-right-radius: 4px;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.15);
    }

    .msg-bubble p {
        margin: 0 0 10px 0;
    }

    .msg-bubble p:last-child {
        margin-bottom: 0;
    }

    .msg-bubble strong {
        color: #ffffff;
    }

    /* Suggestions chips */
    .chat-suggestions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        padding: 12px 24px;
        background: rgba(10, 12, 28, 0.3);
        border-top: 1px solid rgba(255, 255, 255, 0.03);
    }

    .suggestion-chip {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: var(--text-sub, rgba(255, 255, 255, 0.6));
        border-radius: 100px;
        padding: 6px 14px;
        font-size: 0.78rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .suggestion-chip:hover {
        background: rgba(239, 68, 68, 0.1);
        border-color: rgba(239, 68, 68, 0.3);
        color: #ffffff;
        transform: translateY(-1px);
    }

    /* Input Box */
    .chat-input-bar {
        padding: 18px 24px;
        background: rgba(10, 12, 28, 0.55);
        border-top: 1px solid rgba(255, 255, 255, 0.06);
    }

    #userInput {
        flex: 1;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 12px 18px;
        color: #ffffff;
        font-size: 0.9rem;
        outline: none;
        transition: all 0.2s;
    }

    #userInput:focus {
        border-color: rgba(239, 68, 68, 0.5);
        background: rgba(255, 255, 255, 0.05);
        box-shadow: 0 0 10px rgba(239, 68, 68, 0.1);
    }

    .btn-send {
        background: #ef4444;
        border: none;
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.35);
    }

    .btn-send:hover {
        background: #dc2626;
        transform: scale(1.03);
    }

    /* Custom SA'EE Delivery Loader */
    .typing-loader .msg-bubble {
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
        box-shadow: none !important;
    }

    .saee-delivery-loader {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 16px 20px;
        width: 220px;
        background: rgba(10, 12, 28, 0.75);
        border: 1px solid rgba(239, 68, 68, 0.35);
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(239, 68, 68, 0.12);
        overflow: hidden;
        margin-top: 4px;
        animation: msg-slide 0.25s cubic-bezier(0.4, 0, 0.2, 1) both;
    }

    /* Delivery Van */
    .delivery-van {
        position: relative;
        width: 60px;
        height: 35px;
        margin-bottom: 8px;
        animation: van-bounce 0.5s infinite ease-in-out alternate;
    }

    .van-body {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 25px;
        background: #ef4444; /* SA'EE Red */
        border-radius: 6px 12px 4px 4px;
        box-shadow: 0 4px 10px rgba(239, 68, 68, 0.4);
    }

    .van-window {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 14px;
        height: 10px;
        background: #0f172a;
        border-radius: 0 6px 0 0;
    }

    .van-stripe {
        position: absolute;
        bottom: 6px;
        left: 0;
        width: 100%;
        height: 3px;
        background: #ffffff;
    }

    .van-wheel {
        position: absolute;
        bottom: 2px;
        width: 10px;
        height: 10px;
        background: #1e293b;
        border: 2px solid #ffffff;
        border-radius: 50%;
        animation: wheel-spin 0.4s infinite linear;
    }

    .front-wheel {
        right: 8px;
    }

    .back-wheel {
        left: 8px;
    }

    /* Road */
    .delivery-road {
        position: relative;
        width: 120px;
        height: 2px;
        background: rgba(255, 255, 255, 0.1);
        margin-bottom: 12px;
        overflow: hidden;
    }

    .road-dash {
        position: absolute;
        top: 0;
        left: 0;
        width: 200%;
        height: 100%;
        background: linear-gradient(to right, 
            transparent 0%, transparent 20%, 
            rgba(255, 255, 255, 0.6) 20%, rgba(255, 255, 255, 0.6) 40%, 
            transparent 40%, transparent 60%, 
            rgba(255, 255, 255, 0.6) 60%, rgba(255, 255, 255, 0.6) 80%,
            transparent 80%, transparent 100%
        );
        animation: road-move 0.7s infinite linear;
    }

    /* Text */
    .delivery-text {
        font-size: 0.78rem;
        color: rgba(255, 255, 255, 0.85);
        font-weight: 600;
        letter-spacing: 0.02em;
        text-align: center;
        animation: text-pulse 1s infinite ease-in-out alternate;
    }

    /* Keyframes */
    @keyframes van-bounce {
        0% { transform: translateY(0); }
        100% { transform: translateY(-4px); }
    }

    @keyframes wheel-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @keyframes road-move {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }

    @keyframes text-pulse {
        0% { opacity: 0.5; }
        100% { opacity: 1; }
    }

    @keyframes msg-slide {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection

@push('scripts')
<script>
    const sessionId = "{{ $sessionId }}";
    const chatMessages = document.getElementById('chatMessages');
    const userInput = document.getElementById('userInput');

    // Helper: format response message helpers (supports simple bold formatting **)
    function formatMarkdown(text) {
        if (!text) return '';
        // Bold
        let formatted = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        // Newlines
        formatted = formatted.replace(/\n/g, '<br>');
        return formatted;
    }

    // Apply suggestions to input field
    function applySuggestion(text) {
        userInput.value = text;
        userInput.focus();
    }

    // Load session chat history on load
    async function loadHistory() {
        try {
            const res = await fetch(`/api/chatbot/history/${sessionId}`);
            const data = await res.json();
            if (data.success && data.data.length > 0) {
                // Clear default welcome message if history exists
                chatMessages.innerHTML = '';
                data.data.forEach(msg => {
                    appendMessage(msg.role, msg.message);
                });
                scrollChat();
            }
        } catch (err) {
            console.error('Failed to load chat history', err);
        }
    }

    // Append single bubble message to conversation pane
    function appendMessage(role, text) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `msg ${role === 'user' ? 'user-msg' : 'bot-msg'}`;
        
        const bubble = document.createElement('div');
        bubble.className = 'msg-bubble';
        bubble.innerHTML = formatMarkdown(text);
        
        msgDiv.appendChild(bubble);
        chatMessages.appendChild(msgDiv);
        scrollChat();
    }

    // Add typing loader indicator bubble
    function showTypingLoader() {
        const loaderDiv = document.createElement('div');
        loaderDiv.className = 'msg bot-msg typing-loader';
        loaderDiv.id = 'typingLoader';
        
        const bubble = document.createElement('div');
        bubble.className = 'msg-bubble';
        bubble.innerHTML = `
            <div class="saee-delivery-loader">
                <div class="delivery-van">
                    <div class="van-body">
                        <div class="van-window"></div>
                        <div class="van-stripe"></div>
                    </div>
                    <div class="van-wheel back-wheel"></div>
                    <div class="van-wheel front-wheel"></div>
                </div>
                <div class="delivery-road">
                    <div class="road-dash"></div>
                </div>
                <div class="delivery-text">
                    ${formatMarkdown("**SA'EE AI** is delivering your answer...")}
                </div>
            </div>`;
        
        loaderDiv.appendChild(bubble);
        chatMessages.appendChild(loaderDiv);
        scrollChat();
    }

    // Remove typing loader bubble
    function hideTypingLoader() {
        const loader = document.getElementById('typingLoader');
        if (loader) loader.remove();
    }

    // Scroll chat conversation container to bottom
    function scrollChat() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Form submit messaging logic
    async function sendMessage(e) {
        e.preventDefault();
        const msg = userInput.value.trim();
        if (!msg) return;

        // Reset inputs
        userInput.value = '';
        appendMessage('user', msg);
        showTypingLoader();

        try {
            const response = await fetch('/api/chatbot/message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    session_id: sessionId,
                    message: msg
                })
            });

            const data = await response.json();
            hideTypingLoader();

            if (data.success && data.reply) {
                appendMessage('assistant', data.reply);
            } else {
                appendMessage('assistant', "I'm sorry, I'm having trouble processing that right now. Please try again.");
            }
        } catch (error) {
            console.error('API Error:', error);
            hideTypingLoader();
            appendMessage('assistant', "Oops! An error occurred connecting to the service. Please verify your connection.");
        }
    }

    // Initialize load
    document.addEventListener('DOMContentLoaded', () => {
        loadHistory();
    });
</script>
@endpush
