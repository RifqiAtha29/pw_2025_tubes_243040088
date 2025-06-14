// Animasi untuk card
document.querySelectorAll('.event-card').forEach(card => {
    card.addEventListener('click', () => {
        window.location.href = card.querySelector('a').href;
    });
});

// Notifikasi
if (window.location.search.includes('login_success=1')) {
    Toastify({
        text: "Login Berhasil!",
        duration: 3000,
        close: true,
        gravity: "top",
        position: "right",
        backgroundColor: "#4CAF50",
    }).showToast();
}
