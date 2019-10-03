<!doctype html>
<html lang="en-GB">
    <head>
        <title>WebAuthn Test Client</title>
    </head>
    <body>
        <form>
            <label for="token">JWT</label>
            <input type="text" id="token" name="token"/>
            <button id="register" type="button">Register</button>
        </form>
        <form>
            <label for="email">Email</label>
            <input type="email" id="email" name="email"/>
            <button id="login" type="button">Login</button>
        </form>
        <script>

            /* UTILITY FUNCTIONS */

            /**
             * @param {string} data
             * @returns {string}
             */
            function base64UrlDecode(data) {
                return window.atob(data.replace(/_/g, '/').replace(/-/g, '+'));
            }

            /**
             * @param {string} data
             * @returns {string}
             */
            function base64Decode(data) {
                return window.atob(data);
            }

            /**
             * @param {Uint8Array} array
             * @returns {string}
             */
            function arrayToBase64String(array) {
                return window.btoa(String.fromCharCode(...array));
            }

            /**
             * @param {string} string
             * @returns {Uint8Array}
             */
            function toByteArray(string) {
                return Uint8Array.from(string, c=>c.charCodeAt(0));
            }



            /* EVENT LISTENER */

            function register() {
                if (!window.fetch || !navigator.credentials || !navigator.credentials.create) {
                    window.alert('Browser not supported.');
                    return;
                }

                window.fetch('/api/webauthn/credential/options', {
                    method: 'POST',
                    cache: 'no-cache',
                    headers: new Headers({
                        'Authorization': 'Bearer ' + document.getElementById('token').value
                    })
                }).then(function(response) {
                    if (!response.ok) {
                        throw new Error('Request failed');
                    }

                    return response.json();

                    // convert base64 to arraybuffer
                }).then(function(options) {

                    options.challenge = toByteArray(base64UrlDecode(options.challenge));
                    options.user.id = toByteArray(base64Decode(options.user.id));

                    return options;

                    // create credentials
                }).then(function(creationOptions) {
                    console.log(creationOptions);
                    return navigator.credentials.create({publicKey: creationOptions});

                    // convert to base64
                }).then(function(data) {

                    return {
                        id: data.id,
                        type: data.type,
                        rawId: arrayToBase64String(new Uint8Array(data.rawId)),
                        name: 'My Yubikey',
                        response: {
                            clientDataJSON: arrayToBase64String(new Uint8Array(data.response.clientDataJSON)),
                            attestationObject: arrayToBase64String(new Uint8Array(data.response.attestationObject)),
                        },
                    };

                    // transfer to server
                }).then(function(data) {
                    return window.fetch('/api/webauthn/credential', {
                        method:'POST',
                        body: JSON.stringify(data),
                        cache:'no-cache',
                        headers: new Headers({
                            'Authorization': 'Bearer ' + document.getElementById('token').value,
                            'Content-Type': 'application/json'
                        })
                    });
                }).then(function(response) {
                    // parse response
                    if (!response.ok) {
                        throw new Error(response.statusText);
                    }

                    // Show success
                    window.alert('Credential successfully registered!');
                }).catch(function(err) {
                    // handle errors
                    window.alert(err.message || 'unknown error occured');
                });
            }

            function doLogin() {
                if (!window.fetch || !navigator.credentials || !navigator.credentials.create) {
                    window.alert('Browser not supported.');
                    return;
                }


                window.fetch('/api/webauthn/login/options', {
                    method: 'POST',
                    cache: 'no-cache',
                    body: JSON.stringify({
                        email: document.getElementById('email').value
                    }),
                    headers: new Headers({'Content-Type': 'application/json'})
                }).then(function(response) {
                    if (!response.ok) {
                        throw new Error('Request failed');
                    }

                    return response.json();

                }).then(function(options) {
                    // convert options
                    options.challenge = toByteArray(base64UrlDecode(options.challenge));
                    if (options.allowCredentials) {
                        options.allowCredentials = options.allowCredentials.map(function(data) {
                            return {
                                ...data,
                                'id': toByteArray(base64UrlDecode(data.id)),
                            };
                        });
                    }

                    return options;

                }).then(function(options) {
                    // request browser API
                    console.log('Requesting credentials from authenticator', options);

                    return navigator.credentials.get({publicKey: options});

                }).then(function(data) {
                    // convert data
                    return {
                        id: data.id,
                        email: document.getElementById('email').value,
                        type: data.type,
                        rawId: arrayToBase64String(new Uint8Array(data.rawId)),
                        response: {
                            authenticatorData: arrayToBase64String(new Uint8Array(data.response.authenticatorData)),
                            clientDataJSON: arrayToBase64String(new Uint8Array(data.response.clientDataJSON)),
                            signature: arrayToBase64String(new Uint8Array(data.response.signature)),
                            userHandle: data.response.userHandle ? arrayToBase64String(new Uint8Array(data.response.userHandle)) : null,
                        },
                    };

                }).then(function(data) {
                    // send to server
                    return window.fetch(
                        '/api/webauthn/login',
                        {
                            method: 'POST',
                            body: JSON.stringify(data),
                            cache: 'no-cache',
                            headers: new Headers({'Content-Type': 'application/json'})
                        }
                    );
                }).then(function(response) {
                    // parse response
                    if (!response.ok) {
                        throw new Error('Request failed');
                    }

                    return response.json();
                }).then(function(user) {
                    // Output response data
                    console.log(user);

                    window.alert('Login success!');
                }).catch(function(err) {
                    // show error
                    window.alert(err.message || 'unknown error occured');
                });
            }

            document.getElementById('login').addEventListener('click', doLogin);
            document.getElementById('register').addEventListener('click', register);
        </script>
    </body>
</html>