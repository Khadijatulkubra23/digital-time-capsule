// Redirect on button click
document.getElementById('startBtn').addEventListener('click', () => {
    window.location.href = 'register.html';
});

// Optional: Auto-redirect after 7 seconds
setTimeout(() => {
    window.location.href = 'register.html';
}, 7000);
