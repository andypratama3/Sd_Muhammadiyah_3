(function () {
    "use strict";

    let forms = document.querySelectorAll('.php-email-form');

    forms.forEach(function (form) {
      form.addEventListener('submit', function (event) {
        event.preventDefault();

        let thisForm = this;

        let action = thisForm.getAttribute('action');
        let recaptcha = thisForm.getAttribute('data-recaptcha-site-key');

        if (!action) {
          displayError(thisForm, 'The form action property is not set!');
          return;
        }
        thisForm.querySelector('.loading').classList.add('d-block');
        thisForm.querySelector('.error-message').classList.remove('d-block');
        thisForm.querySelector('.sent-message').classList.remove('d-block');

        let formData = new FormData(thisForm);

        if (recaptcha) {
          if (typeof grecaptcha !== "undefined") {
            grecaptcha.ready(function () {
              try {
                grecaptcha.execute(recaptcha, { action: 'php_email_form_submit' })
                  .then(token => {
                    formData.set('recaptcha-response', token);
                    php_email_form_submit(thisForm, action, formData);
                  })
              } catch (error) {
                displayError(thisForm, error);
              }
            });
          } else {
            displayError(thisForm, 'The reCaptcha javascript API url is not loaded!')
          }
        } else {
          php_email_form_submit(thisForm, action, formData);
        }
      });
    });

    function php_email_form_submit(thisForm, action, formData) {
      fetch(action, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(response => {
          if (response.ok) {
            return response.json(); // Parse response as JSON
          } else {
            throw new Error(`${response.status} ${response.statusText} ${response.url}`);
          }
        })
        .then(data => {
          thisForm.querySelector('.loading').classList.remove('d-block');
          if (data.message) {
            // Display success message
            thisForm.querySelector('.sent-message').innerHTML = data.message;
            thisForm.querySelector('.sent-message').classList.add('d-block');
            thisForm.reset();
          } else {
            // Handle the case where the response does not contain a message
            throw new Error('Form submission successful but no success message returned from the server');
          }
        })
        .catch((error) => {
          displayError(thisForm, error);
        });
    }

    function displayError(thisForm, error) {
      thisForm.querySelector('.loading').classList.remove('d-block');
      thisForm.querySelector('.error-message').innerHTML = error;
      thisForm.querySelector('.error-message').classList.add('d-block');
    }

  })();
