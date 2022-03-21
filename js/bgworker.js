window.addEventListener('load', function () {
    let stopButton = document.getElementById('stop-button');
    stopButton.addEventListener('click', function () {
        if (confirm('Остановить выполнение?')) {
            this.closest('form').submit();
        }
    });
});
