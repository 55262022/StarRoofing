<!-- Chat Widget Styles -->
<style>
    .chat-widget-button {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #e9b949 0%, #d4a943 100%);
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 20px rgba(233, 185, 73, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        z-index: 9998;
    }

    .chat-widget-button:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 30px rgba(233, 185, 73, 0.6);
    }

    .chat-widget-button svg {
        width: 28px;
        height: 28px;
        fill: #1a1a2e;
    }

    .chat-widget-popup {
        position: fixed;
        bottom: 100px;
        right: 30px;
        width: 380px;
        height: 550px;
        background: #1a1a2e;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        display: none;
        flex-direction: column;
        overflow: hidden;
        z-index: 9999;
        animation: slideUp 0.3s ease;
        border: 1px solid rgba(233, 185, 73, 0.2);
    }

    .chat-widget-popup.active {
        display: flex;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .chat-widget-header {
        background: linear-gradient(135deg, #e9b949 0%, #d4a943 100%);
        color: #1a1a2e;
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .chat-widget-header-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .chat-widget-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: #1a1a2e;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #e9b949;
        font-size: 18px;
    }

    .chat-widget-header-text h3 {
        font-size: 16px;
        font-weight: 700;
        margin: 0;
        letter-spacing: 0.5px;
    }

    .chat-widget-header-text p {
        font-size: 12px;
        opacity: 0.8;
        margin: 0;
    }

    .chat-widget-close-button {
        background: none;
        border: none;
        color: #1a1a2e;
        cursor: pointer;
        font-size: 28px;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0.8;
        transition: opacity 0.2s;
        font-weight: 300;
        line-height: 1;
    }

    .chat-widget-close-button:hover {
        opacity: 1;
    }

    .chat-widget-messages {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        background: #0f0f0f;
    }

    .chat-widget-messages::-webkit-scrollbar {
        width: 6px;
    }

    .chat-widget-messages::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
    }

    .chat-widget-messages::-webkit-scrollbar-thumb {
        background: rgba(233, 185, 73, 0.3);
        border-radius: 3px;
    }

    .chat-widget-messages::-webkit-scrollbar-thumb:hover {
        background: rgba(233, 185, 73, 0.5);
    }

    .chat-widget-message {
        margin-bottom: 16px;
        display: flex;
        gap: 10px;
        animation: messageSlide 0.3s ease;
    }

    @keyframes messageSlide {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .chat-widget-message.bot {
        justify-content: flex-start;
    }

    .chat-widget-message.user {
        justify-content: flex-end;
    }

    .chat-widget-message-bubble {
        max-width: 75%;
        padding: 12px 16px;
        border-radius: 18px;
        font-size: 14px;
        line-height: 1.5;
    }

    .chat-widget-message.bot .chat-widget-message-bubble {
        background: rgba(255, 255, 255, 0.05);
        color: rgba(255, 255, 255, 0.9);
        border-bottom-left-radius: 4px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .chat-widget-message.user .chat-widget-message-bubble {
        background: linear-gradient(135deg, #e9b949 0%, #d4a943 100%);
        color: #1a1a2e;
        border-bottom-right-radius: 4px;
        font-weight: 500;
    }

    /* Quick Questions */
    .chat-quick-questions {
        padding: 15px 20px;
        background: #0f0f0f;
        border-top: 1px solid rgba(233, 185, 73, 0.1);
    }

    .chat-quick-questions-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .chat-quick-questions-title {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.6);
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .chat-quick-questions-toggle {
        background: none;
        border: none;
        color: #e9b949;
        cursor: pointer;
        font-size: 14px;
        padding: 5px;
        transition: transform 0.3s ease;
    }

    .chat-quick-questions-toggle:hover {
        color: #d4a943;
    }

    .chat-quick-questions-toggle.collapsed {
        transform: rotate(180deg);
    }

    .chat-quick-questions-grid {
        display: grid;
        gap: 8px;
        max-height: 200px;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }

    .chat-quick-questions-grid.collapsed {
        max-height: 0;
    }

    .chat-quick-question-btn {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.8);
        padding: 10px 14px;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 13px;
        text-align: left;
        font-family: 'Montserrat', sans-serif;
    }

    .chat-quick-question-btn:hover {
        background: rgba(233, 185, 73, 0.1);
        border-color: rgba(233, 185, 73, 0.3);
        color: #e9b949;
        transform: translateX(5px);
    }

    /* Sign in Offer Button */
    .chat-signin-offer {
        padding: 15px 20px;
        background: rgba(233, 185, 73, 0.05);
        border-top: 1px solid rgba(233, 185, 73, 0.1);
        text-align: center;
    }

    .chat-signin-offer-text {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 10px;
        line-height: 1.4;
    }

    .chat-signin-offer-btn {
        background: linear-gradient(135deg, #e9b949 0%, #d4a943 100%);
        border: none;
        color: #1a1a2e;
        padding: 10px 24px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-family: 'Montserrat', sans-serif;
        letter-spacing: 0.5px;
    }

    .chat-signin-offer-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(233, 185, 73, 0.4);
    }

    /* Login Form */
    .chat-login-form {
        padding: 20px;
        background: #0f0f0f;
        border-top: 1px solid rgba(233, 185, 73, 0.2);
    }

    .chat-login-form-title {
        font-size: 14px;
        color: #e9b949;
        margin-bottom: 15px;
        font-weight: 600;
        text-align: center;
    }

    .chat-login-form-subtitle {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.6);
        margin-bottom: 15px;
        text-align: center;
    }

    .chat-form-group {
        margin-bottom: 12px;
    }

    .chat-form-input {
        width: 100%;
        padding: 10px 14px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        color: white;
        font-size: 13px;
        font-family: 'Montserrat', sans-serif;
        transition: all 0.3s ease;
    }

    .chat-form-input::placeholder {
        color: rgba(255, 255, 255, 0.3);
    }

    .chat-form-input:focus {
        outline: none;
        border-color: rgba(233, 185, 73, 0.5);
        background: rgba(255, 255, 255, 0.08);
    }

    .chat-form-submit {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #e9b949 0%, #d4a943 100%);
        border: none;
        border-radius: 10px;
        color: #1a1a2e;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-family: 'Montserrat', sans-serif;
        letter-spacing: 0.5px;
    }

    .chat-form-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(233, 185, 73, 0.4);
    }

    .chat-form-back {
        width: 100%;
        padding: 12px;
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        color: rgba(255, 255, 255, 0.7);
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-family: 'Montserrat', sans-serif;
        letter-spacing: 0.5px;
        margin-top: 10px;
    }

    .chat-form-back:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(233, 185, 73, 0.3);
        color: #e9b949;
    }

    /* Input Container */
    .chat-widget-input-container {
        padding: 20px;
        background: #1a1a2e;
        border-top: 1px solid rgba(233, 185, 73, 0.2);
    }

    .chat-widget-input-wrapper {
        display: flex;
        gap: 10px;
    }

    .chat-widget-input {
        flex: 1;
        padding: 12px 18px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 25px;
        font-size: 14px;
        outline: none;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.05);
        color: white;
        font-family: 'Montserrat', sans-serif;
    }

    .chat-widget-input::placeholder {
        color: rgba(255, 255, 255, 0.4);
    }

    .chat-widget-input:focus {
        border-color: rgba(233, 185, 73, 0.5);
        background: rgba(255, 255, 255, 0.08);
    }

    .chat-widget-send-button {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #e9b949 0%, #d4a943 100%);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .chat-widget-send-button:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 15px rgba(233, 185, 73, 0.4);
    }

    .chat-widget-send-button svg {
        width: 20px;
        height: 20px;
        fill: #1a1a2e;
    }

    .hidden {
        display: none !important;
    }

    /* Responsive Design */
    @media screen and (max-width: 768px) {
        .chat-widget-button {
            right: 20px;
            bottom: 20px;
            width: 55px;
            height: 55px;
        }

        .chat-widget-popup {
            right: 10px;
            width: calc(100% - 20px);
            bottom: 85px;
            max-width: 380px;
        }
    }

    @media screen and (max-width: 480px) {
        .chat-widget-popup {
            height: 500px;
        }
    }

    @media screen and (max-width: 768px) {
        body:not(.sidebar-collapsed) .chat-widget-button,
        body:not(.sidebar-collapsed) .chat-widget-popup,
        body.sidebar-collapsed .chat-widget-button,
        body.sidebar-collapsed .chat-widget-popup {
            right: 20px;
        }
    }
</style>

<!-- Chat Widget HTML -->
<button class="chat-widget-button" id="chatWidgetButton">
    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/>
    </svg>
</button>

<div class="chat-widget-popup" id="chatWidgetPopup">
    <div class="chat-widget-header">
        <div class="chat-widget-header-info">
            <div class="chat-widget-avatar">SR</div>
            <div class="chat-widget-header-text">
                <h3>Star Roofing</h3>
                <p>We're here to help!</p>
            </div>
        </div>
        <button class="chat-widget-close-button" id="chatWidgetCloseButton">&times;</button>
    </div>

    <div class="chat-widget-messages" id="chatWidgetMessages">
        <div class="chat-widget-message bot">
            <div class="chat-widget-message-bubble">
                👋 Welcome to Star Roofing & Construction! How can we assist you with your roofing or construction needs today?
            </div>
        </div>
    </div>

    <!-- Quick Questions Section -->
    <div class="chat-quick-questions" id="chatQuickQuestions">
        <div class="chat-quick-questions-header">
            <div class="chat-quick-questions-title">QUICK QUESTIONS</div>
            <button class="chat-quick-questions-toggle" id="quickQuestionsToggle">
                <i class="fas fa-chevron-up"></i>
            </button>
        </div>
        <div class="chat-quick-questions-grid" id="quickQuestionsGrid">
            <button class="chat-quick-question-btn" data-question="What services do you offer?">
                What services do you offer?
            </button>
            <button class="chat-quick-question-btn" data-question="How can I get a free estimate?">
                How can I get a free estimate?
            </button>
            <button class="chat-quick-question-btn" data-question="What are your business hours?">
                What are your business hours?
            </button>
            <button class="chat-quick-question-btn" data-question="Do you offer warranties?">
                Do you offer warranties?
            </button>
        </div>
    </div>

    <!-- Sign in Offer (shown after bot responses for non-logged users) -->
    <div class="chat-signin-offer hidden" id="chatSigninOffer">
        <div class="chat-signin-offer-text">
            Want personalized updates and priority support? Sign in to continue the conversation!
        </div>
        <button class="chat-signin-offer-btn" id="signinOfferBtn">Sign In for Updates</button>
    </div>

    <!-- Login Form (shown when user tries to send custom message) -->
    <div class="chat-login-form hidden" id="chatLoginForm">
        <div class="chat-login-form-title">Sign in to send a message</div>
        <div class="chat-login-form-subtitle">Please provide your details to continue</div>
        <form id="chatLoginFormElement">
            <div class="chat-form-group">
                <input type="text" class="chat-form-input" name="first_name" placeholder="First Name" required>
            </div>
            <div class="chat-form-group">
                <input type="text" class="chat-form-input" name="last_name" placeholder="Last Name" required>
            </div>
            <div class="chat-form-group">
                <input type="email" class="chat-form-input" name="email" placeholder="Email Address" required>
            </div>
            <button type="submit" class="chat-form-submit">Continue to Chat</button>
            <button type="button" class="chat-form-back" id="chatFormBackButton">Back</button>
        </form>
    </div>

    <!-- Input Container -->
    <div class="chat-widget-input-container" id="chatInputContainer">
        <div class="chat-widget-input-wrapper">
            <input 
                type="text" 
                class="chat-widget-input" 
                id="chatWidgetInput" 
                placeholder="Type your message..."
                autocomplete="off"
            >
            <button class="chat-widget-send-button" id="chatWidgetSendButton">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- Chat Widget Script -->
<script>
(function() {
    // Check if user is logged in (check PHP session)
    const isLoggedIn = <?php echo isset($_SESSION['account_id']) ? 'true' : 'false'; ?>;
    let guestUser = null; // Store guest user info
    let pendingMessage = ''; // Store message typed before login
    
    const chatButton = document.getElementById('chatWidgetButton');
    const chatPopup = document.getElementById('chatWidgetPopup');
    const closeButton = document.getElementById('chatWidgetCloseButton');
    const chatInput = document.getElementById('chatWidgetInput');
    const sendButton = document.getElementById('chatWidgetSendButton');
    const chatMessages = document.getElementById('chatWidgetMessages');
    const quickQuestions = document.getElementById('chatQuickQuestions');
    const loginForm = document.getElementById('chatLoginForm');
    const loginFormElement = document.getElementById('chatLoginFormElement');
    const inputContainer = document.getElementById('chatInputContainer');
    const formBackButton = document.getElementById('chatFormBackButton');
    const quickQuestionsToggle = document.getElementById('quickQuestionsToggle');
    const quickQuestionsGrid = document.getElementById('quickQuestionsGrid');
    const signinOffer = document.getElementById('chatSigninOffer');
    const signinOfferBtn = document.getElementById('signinOfferBtn');

    // Toggle chat popup
    chatButton.addEventListener('click', function() {
        chatPopup.classList.toggle('active');
        if (chatPopup.classList.contains('active')) {
            chatInput.focus();
        }
    });

    // Close chat popup
    closeButton.addEventListener('click', function() {
        chatPopup.classList.remove('active');
    });

    // Toggle quick questions
    quickQuestionsToggle.addEventListener('click', function() {
        quickQuestionsGrid.classList.toggle('collapsed');
        this.classList.toggle('collapsed');
    });

    // Sign in offer button click
    signinOfferBtn.addEventListener('click', function() {
        showLoginForm();
    });

    // Back button click
    formBackButton.addEventListener('click', function() {
        hideLoginForm();
        // Clear the pending message
        pendingMessage = '';
        // Restore the message in input
        chatInput.focus();
    });

    // Handle quick question buttons
    document.querySelectorAll('.chat-quick-question-btn').forEach(button => {
        button.addEventListener('click', function() {
            const question = this.getAttribute('data-question');
            addMessage(question, 'user');
            
            // Collapse quick questions after selection
            quickQuestionsGrid.classList.add('collapsed');
            quickQuestionsToggle.classList.add('collapsed');
            
            // Bot responses for quick questions
            setTimeout(function() {
                let response = '';
                switch(question) {
                    case 'What services do you offer?':
                        response = 'We offer Roofing Installation, Roof Repair & Maintenance, Construction Services, and Renovation & Remodeling. Would you like to know more about any specific service?';
                        break;
                    case 'How can I get a free estimate?':
                        response = 'You can get a free estimate by calling us at (044) 329-0881 or visiting our office at San Juan Accfa District, Cabanatuan City. We\'ll be happy to assess your project!';
                        break;
                    case 'What are your business hours?':
                        response = 'We\'re open Monday to Saturday, 8:00 AM - 5:00 PM. For urgent matters, you can reach us at 0908-620-2381 or 0933-628-3312.';
                        break;
                    case 'Do you offer warranties?':
                        response = 'Yes! We offer comprehensive warranties on all our roofing and construction projects. The specific warranty details depend on the service and materials used. Contact us for more information!';
                        break;
                }
                addMessage(response, 'bot');
                
                // Show sign in offer for non-logged users after bot response
                if (!isLoggedIn && !guestUser) {
                    setTimeout(function() {
                        signinOffer.classList.remove('hidden');
                    }, 500);
                }
            }, 1000);
        });
    });

    // Show login form
    function showLoginForm() {
        quickQuestions.classList.add('hidden');
        inputContainer.classList.add('hidden');
        signinOffer.classList.add('hidden');
        loginForm.classList.remove('hidden');
    }

    // Hide login form
    function hideLoginForm() {
        loginForm.classList.add('hidden');
        quickQuestions.classList.remove('hidden');
        inputContainer.classList.remove('hidden');
    }

    // Handle login form submission
    loginFormElement.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        guestUser = {
            first_name: formData.get('first_name'),
            last_name: formData.get('last_name'),
            email: formData.get('email')
        };

        // Hide login form and sign in offer, show chat
        hideLoginForm();
        signinOffer.classList.add('hidden');
        
        // Add welcome message
        addMessage(`Welcome, ${guestUser.first_name}! You can now send messages.`, 'bot');
        
        // Send the pending message if there was one
        if (pendingMessage) {
            addMessage(pendingMessage, 'user');
            saveMessageToDatabase(pendingMessage);
            pendingMessage = '';
            
            // Bot response
            setTimeout(function() {
                addMessage('Thank you for your message! A member of our team will respond to you shortly. For immediate assistance, please call us at (044) 329-0881.', 'bot');
            }, 1000);
        }
        
        // Focus on input
        chatInput.focus();
    });

    // Send message function
    function sendMessage() {
        const message = chatInput.value.trim();
        if (message) {
            // Check if user is logged in or has signed up as guest
            if (!isLoggedIn && !guestUser) {
                // Store the message and show login form
                pendingMessage = message;
                chatInput.value = '';
                showLoginForm();
                addMessage('Please sign in to send your message.', 'bot');
                return;
            }

            // User is logged in or has signed up, send the message
            addMessage(message, 'user');
            chatInput.value = '';
            
            // Save message to database via AJAX
            saveMessageToDatabase(message);
            
            // Simulate bot response
            setTimeout(function() {
                addMessage('Thank you for your message! A member of our team will respond to you shortly. For immediate assistance, please call us at (044) 329-0881.', 'bot');
            }, 1000);
        }
    }

    // Save message to database
    function saveMessageToDatabase(message) {
        const data = {
            message: message,
            user_type: isLoggedIn ? 'registered' : 'guest'
        };

        if (guestUser) {
            data.first_name = guestUser.first_name;
            data.last_name = guestUser.last_name;
            data.email = guestUser.email;
        }

        // AJAX call to save message
        fetch('save_chat_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            console.log('Message saved:', data);
        })
        .catch(error => {
            console.error('Error saving message:', error);
        });
    }

    // Send button click
    sendButton.addEventListener('click', sendMessage);

    // Enter key to send
    chatInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    // Add message to chat
    function addMessage(text, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'chat-widget-message ' + sender;
        
        const bubbleDiv = document.createElement('div');
        bubbleDiv.className = 'chat-widget-message-bubble';
        bubbleDiv.textContent = text;
        
        messageDiv.appendChild(bubbleDiv);
        chatMessages.appendChild(messageDiv);
        
        // Scroll to bottom
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
})();
</script>