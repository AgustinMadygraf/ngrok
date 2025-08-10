/*
Path: main.js
*/

window.onload = function() {
    console.log('window.onload triggered');
    const params = new URLSearchParams(window.location.search);
    const container = document.getElementById('container');
    const url = 'redirect.php'; // PHP endpoint returns the URL
    console.log('Fetching:', url);

    try {
        fetch(url)
            .then(response => {
                console.log('Fetch response:', response);
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Received data:', data);
                if (params.get('forward') === 'true') {
                    console.log('Redirecting to:', data.url);
                    window.location.href = data.url;
                } else {
                    console.log('Embedding iframe with src:', data.url);
                    container.innerHTML = `<iframe id="myIframe" src="${data.url}" style="border:0;width:100%;height:98vh;display:block;" allowfullscreen></iframe>`;
                }
            })
            .catch(error => {
                console.error('Fetch or processing error:', error);
            });
    } catch (err) {
        console.error('Outer try/catch error:', err);
    }
};