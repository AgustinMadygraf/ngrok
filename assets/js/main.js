/*
Path: main.js
*/

window.onload = function() {
    const params = new URLSearchParams(window.location.search);
    const container = document.getElementById('container');
    const url = 'redirect.php'; // PHP endpoint returns the URL

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (params.get('forward') === 'true') {
                window.location.href = data.url;
            } else {
            container.innerHTML = `<iframe id="myIframe" src="${data.url}" style="border:0;width:100%;height:98vh;display:block;" allowfullscreen></iframe>`;
            }
        });
};