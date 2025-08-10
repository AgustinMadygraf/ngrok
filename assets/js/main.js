/*
Path: main.js
*/

window.onload = function() {
    console.log('window.onload triggered');
    const params = new URLSearchParams(window.location.search);
    const container = document.getElementById('container');
    const url = 'redirect.php'; // PHP endpoint returns the URL
    console.log('Fetching:', url);
    console.log('URLSearchParams:', params.toString());

    try {
        fetch(url)
            .then(response => {
                console.log('Fetch response:', response);
                console.log('Response status:', response.status);
                console.log('Response redirected:', response.redirected);
                console.log('Response URL:', response.url);
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.text().then(text => {
                    console.log('Raw response text:', text);
                    try {
                        const data = JSON.parse(text);
                        return data;
                    } catch (jsonErr) {
                        console.error('JSON parse error:', jsonErr);
                        throw new Error('Invalid JSON: ' + jsonErr.message + ' | Raw: ' + text);
                    }
                });
            })
            .then(data => {
                console.log('Received data:', data);
                if (!data || typeof data.url === 'undefined') {
                    console.error('No "url" key in response:', data);
                    container.innerHTML = `<pre style="color:red;">Respuesta inválida del servidor</pre>`;
                    return;
                }
                if (data.url === null && data.redirect) {
                    console.log('No URL found, redirecting to:', data.redirect);
                    window.location.href = data.redirect;
                    return;
                }
                if (params.get('forward') !== 'false') {
                    console.log('Redirecting to:', data.url);
                    window.location.href = data.url;
                } else {
                    console.log('Embedding iframe with src:', data.url);
                    container.innerHTML = `<iframe id="myIframe" src="${data.url}" style="border:0;width:100%;height:98vh;display:block;" allowfullscreen></iframe>`;
                }
            })
            .catch(error => {
                console.error('Fetch or processing error:', error);
                if (container) {
                    container.innerHTML = `<pre style="color:red;">${error}</pre>`;
                }
            });
    } catch (err) {
        console.error('Outer try/catch error:', err);
        if (container) {
            container.innerHTML = `<pre style="color:red;">${err}</pre>`;
        }
    }
};