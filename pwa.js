if ('serviceWorker' in navigator) {
  window.addEventListener('load', function () {
    navigator.serviceWorker
      .register('/pos/sw.js')
      .catch(function (error) {
        console.warn('Service worker registration failed:', error);
      });
  });
}
