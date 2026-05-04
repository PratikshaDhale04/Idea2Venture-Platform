// Idea2Venture JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initAnimations();
    initTooltips();
    initChatAutoRefresh();
    initLikeButtons();
    initCommentForm();
    initSearchFilter();
});

function initAnimations() {
    const elements = document.querySelectorAll('.fade-in-element');
    elements.forEach((el, index) => {
        el.style.animationDelay = `${index * 0.1}s`;
        el.classList.add('fade-in');
    });
}

function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

function showToast(message, type = 'success') {
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast-custom toast-${type}`;
    toast.innerHTML = `
        <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-x-circle-fill'}"></i>
        <span>${message}</span>
    `;
    toastContainer.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideInRight 0.5s ease reverse';
        setTimeout(() => toast.remove(), 500);
    }, 3000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
    return container;
}

// Like System
function initLikeButtons() {
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const ideaId = this.dataset.ideaId;
            toggleLike(ideaId, this);
        });
    });
}

async function toggleLike(ideaId, button) {
    try {
        const response = await fetch('api/like.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ idea_id: ideaId })
        });
        const data = await response.json();
        
        if (data.success) {
            button.classList.toggle('liked');
            const countEl = button.nextElementSibling;
            if (countEl) {
                countEl.textContent = data.likes;
            }
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        showToast('Error processing like', 'error');
    }
}

// Comment System
function initCommentForm() {
    const forms = document.querySelectorAll('.comment-form');
    forms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const ideaId = this.dataset.ideaId;
            const input = this.querySelector('input[name="comment"]');
            const comment = input.value.trim();
            
            if (!comment) return;
            
            try {
                const response = await fetch('api/comment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ idea_id: ideaId, comment: comment })
                });
                const data = await response.json();
                
                if (data.success) {
                    addCommentToUI(data.comment);
                    input.value = '';
                    showToast('Comment added!', 'success');
                } else {
                    showToast(data.message, 'error');
                }
            } catch (error) {
                showToast('Error adding comment', 'error');
            }
        });
    });
}

function addCommentToUI(comment) {
    const container = document.querySelector('.comments-list');
    if (!container) return;
    
    const commentEl = document.createElement('div');
    commentEl.className = 'comment-item';
    commentEl.innerHTML = `
        <div class="d-flex align-items-center gap-2 mb-2">
            <div class="comment-avatar">${comment.user_name.charAt(0).toUpperCase()}</div>
            <strong>${comment.user_name}</strong>
            <small class="text-muted">${comment.time_ago}</small>
        </div>
        <p class="mb-0">${comment.comment}</p>
    `;
    container.insertBefore(commentEl, container.firstChild);
}

// Search & Filter
function initSearchFilter() {
    const searchInput = document.querySelector('#searchInput');
    const categoryFilter = document.querySelector('#categoryFilter');
    
    if (searchInput) {
        searchInput.addEventListener('input', debounce(filterIdeas, 300));
    }
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterIdeas);
    }
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

async function filterIdeas() {
    const search = document.querySelector('#searchInput')?.value || '';
    const category = document.querySelector('#categoryFilter')?.value || '';
    
    try {
        const response = await fetch(`api/ideas.php?search=${search}&category=${category}`);
        const data = await response.json();
        
        if (data.success) {
            updateIdeasGrid(data.ideas);
        }
    } catch (error) {
        console.error('Filter error:', error);
    }
}

function updateIdeasGrid(ideas) {
    const grid = document.querySelector('#ideasGrid');
    if (!grid) return;
    
    grid.innerHTML = ideas.map(idea => createIdeaCard(idea)).join('');
    initLikeButtons();
    initCommentForm();
}

function createIdeaCard(idea) {
    return `
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="glass-card idea-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="idea-category">${idea.category}</span>
                    <span class="idea-status status-${idea.status}">${idea.status}</span>
                </div>
                <h5 class="mb-3">${idea.title}</h5>
                <p class="text-muted mb-3">${idea.description.substring(0, 100)}...</p>
                <div class="mb-3">
                    <small class="text-muted"><i class="bi bi-tag"></i> ${idea.skills_required || 'No skills specified'}</small>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <button class="like-btn ${idea.liked ? 'liked' : ''}" data-idea-id="${idea.id}">
                        <i class="bi ${idea.liked ? 'bi-heart-fill' : 'bi-heart'}"></i>
                    </button>
                    <span class="text-muted">${idea.likes_count} likes</span>
                    <a href="idea_detail.php?id=${idea.id}" class="btn btn-sm btn-outline-gradient">View</a>
                </div>
            </div>
        </div>
    `;
}

// Chat Auto Refresh
function initChatAutoRefresh() {
    if (!document.querySelector('.chat-messages')) return;
    
    setInterval(loadMessages, 3000);
}

async function loadMessages() {
    const receiverId = document.querySelector('#receiverId')?.value;
    if (!receiverId) return;
    
    try {
        const response = await fetch(`api/messages.php?receiver_id=${receiverId}`);
        const data = await response.json();
        
        if (data.success) {
            updateChatMessages(data.messages);
        }
    } catch (error) {
        console.error('Error loading messages:', error);
    }
}

function updateChatMessages(messages) {
    const container = document.querySelector('.chat-messages');
    if (!container) return;
    
    const currentUserId = parseInt(document.querySelector('#currentUserId')?.value || 0);
    
    container.innerHTML = messages.map(msg => `
        <div class="message ${msg.sender_id === currentUserId ? 'message-sent' : 'message-received'}">
            <p class="mb-1">${msg.message}</p>
            <small class="opacity-75">${msg.time_ago}</small>
        </div>
    `).join('');
    
    container.scrollTop = container.scrollHeight;
}

// Send Message
document.querySelectorAll('.send-message-form').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const receiverId = this.querySelector('#receiverId').value;
        const messageInput = this.querySelector('input[name="message"]');
        const message = messageInput.value.trim();
        
        if (!message) return;
        
        try {
            const response = await fetch('api/send_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ receiver_id: receiverId, message: message })
            });
            const data = await response.json();
            
            if (data.success) {
                messageInput.value = '';
                loadMessages();
                showToast('Message sent!', 'success');
            } else {
                showToast(data.message, 'error');
            }
        } catch (error) {
            showToast('Error sending message', 'error');
        }
    });
});

// Join Request
async function sendJoinRequest(ideaId) {
    try {
        const response = await fetch('api/join_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ idea_id: ideaId })
        });
        const data = await response.json();
        
        showToast(data.message, data.success ? 'success' : 'error');
        
        if (data.success) {
            const btn = document.querySelector(`[data-idea-id="${ideaId}"].join-request-btn`);
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Request Pending';
            }
        }
    } catch (error) {
        showToast('Error sending request', 'error');
    }
}

// Accept/Reject Request
async function handleRequest(requestId, action) {
    try {
        const response = await fetch('api/handle_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ request_id: requestId, action: action })
        });
        const data = await response.json();
        
        showToast(data.message, data.success ? 'success' : 'error');
        
        if (data.success) {
            setTimeout(() => location.reload(), 1000);
        }
    } catch (error) {
        showToast('Error processing request', 'error');
    }
}

// Delete with confirmation
function confirmDelete(itemType, itemId) {
    if (confirm(`Are you sure you want to delete this ${itemType}?`)) {
        window.location.href = `delete.php?type=${itemType}&id=${itemId}`;
    }
}

// Form Validation
function validatePassword(password) {
    const minLength = password.length >= 8;
    const hasNumber = /\d/.test(password);
    const hasSymbol = /[!@#$%^&*(),.?":{}|<>]/.test(password);
    
    return minLength && hasNumber && hasSymbol;
}

document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const passwordInput = this.querySelector('input[name="password"]');
        if (passwordInput && !validatePassword(passwordInput.value)) {
            e.preventDefault();
            showToast('Password must be at least 8 characters with a number and symbol', 'error');
        }
    });
});

// Mobile menu toggle
const mobileMenuBtn = document.querySelector('.navbar-toggler');
if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', function() {
        document.querySelector('.navbar-collapse').classList.toggle('show');
    });
}

// Add loading animation to buttons
document.querySelectorAll('.btn').forEach(btn => {
    btn.addEventListener('click', function() {
        if (this.tagName === 'A' && this.getAttribute('href') !== '#' && !this.hasAttribute('data-bs-toggle')) {
            this.innerHTML = '<span class="loading-spinner" style="width: 20px; height: 20px;"></span>';
        }
    });
});

// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal').forEach(modal => {
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) modalInstance.hide();
        });
    }
});

// Initialize on scroll animations
window.addEventListener('scroll', function() {
    const scrolled = window.pageYOffset;
    document.querySelectorAll('.parallax').forEach(el => {
        el.style.transform = `translateY(${scrolled * 0.5}px)`;
    });
});

// Live character count for textareas
document.querySelectorAll('textarea').forEach(textarea => {
    const maxLength = textarea.getAttribute('maxlength');
    if (maxLength) {
        const counter = document.createElement('div');
        counter.className = 'text-end text-muted small mt-1';
        counter.innerHTML = `0/${maxLength}`;
        textarea.parentNode.appendChild(counter);
        
        textarea.addEventListener('input', function() {
            counter.innerHTML = `${this.value.length}/${maxLength}`;
        });
    }
});

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Copied to clipboard!', 'success');
    }).catch(() => {
        showToast('Failed to copy', 'error');
    });
}

// Export functionality
function exportToCSV(data, filename) {
    const csv = convertToCSV(data);
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
}

function convertToCSV(data) {
    const headers = Object.keys(data[0]);
    const rows = data.map(row => headers.map(h => row[h]).join(','));
    return [headers.join(','), ...rows].join('\n');
}

// Initialize feather icons if available
if (typeof feather !== 'undefined') {
    feather.replace();
}

// Console message
console.log('%c Idea2Venture ', 'background: #667eea; color: white; padding: 10px; border-radius: 5px; font-size: 20px;');
console.log('%c Welcome to the platform! ', 'background: #764ba2; color: white; padding: 5px; border-radius: 3px;');