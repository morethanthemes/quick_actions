(function (Drupal, once) {

  Drupal.behaviors.setDefaultButton = {
    attach(context) {
      once('set-default-button', '#set-as-default-button', context).forEach(function (element) {
        element.addEventListener('click', function (e) {
          e.preventDefault();
            sendDefaultPath(window.location.pathname);
        });
      });
    }
  };

  function sendDefaultPath(path) {
    fetch('/quick-actions/set-default-path', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-Token': drupalSettings.ajaxPageState.csrfToken
      },
      body: new URLSearchParams({ path })
    })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === 'success') {
        alert(data.message);
      } else {
        alert('⚠️ Error: ' + data.message);
      }
    })
    .catch((error) => {
      console.error('AJAX error:', error);
      alert('⚠️ AJAX request failed.');
    });
  }


})(Drupal, once);
