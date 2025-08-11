/*
Path: assets/js/main.js
*/

class UrlParamsHandler {
    constructor() {
        this.params = new URLSearchParams(window.location.search);
    }
    
    getParam(key) {
        return this.params.get(key);
    }
    
    getAllParams() {
        return Object.fromEntries(this.params.entries());
    }
}

class ApiFetcher {
    async fetchData(url) {
        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error(`Network error: ${response.status}`);
        }
        
        const text = await response.text();
        
        try {
            return JSON.parse(text);
        } catch (error) {
            throw new Error(`JSON parse error: ${error.message}`);
        }
    }
}

class ResponseHandler {
    constructor(redirectManager, uiRenderer) {
        this.redirectManager = redirectManager;
        this.uiRenderer = uiRenderer;
    }
    
    handleResponse(data) {
        if (!data || typeof data.url === 'undefined') {
            this.uiRenderer.showError('Respuesta inválida del servidor');
            return;
        }
        
        if (data.url === null && data.redirect) {
            this.redirectManager.handleNoUrlCase(data.redirect);
            return;
        }
        
        if (typeof data.url === 'string' && data.url !== '') {
            return data.url;
        }
        
        if (data.error) {
            this.uiRenderer.showError(`Error de conexión: ${data.error}`);
            return;
        }
        
        this.uiRenderer.showError('URL inválida recibida del servidor');
    }
}

class RedirectManager {
    constructor(uiRenderer) {
        this.uiRenderer = uiRenderer;
    }
    
    redirect(url, delay = 0) {
        if (delay > 0) {
            this.uiRenderer.showMessage(
                `Redirigiendo a ${url}...`, 
                'orange'
            );
            
            setTimeout(() => {
                window.location.href = url;
            }, delay);
        } else {
            window.location.href = url;
        }
    }
    
    handleNoUrlCase(redirectUrl) {
        this.redirect(redirectUrl, 2000);
    }
}

class UIRenderer {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        if (!this.container) {
            throw new Error(`Container with ID ${containerId} not found`);
        }
    }
    
    renderIframe(url) {
        this.container.innerHTML = `
            <iframe 
                id="contentIframe" 
                src="${url}" 
                style="border:0; width:100%; height:98vh; display:block;" 
                allowfullscreen
            ></iframe>
        `;
        
        const iframe = document.getElementById('contentIframe');
        let loaded = false;
        
        iframe.onload = () => {
            loaded = true;
        };
        
        setTimeout(() => {
            if (!loaded) {
                this.showMessage('El contenido no cargó, redirigiendo...', 'orange');
                this.redirect('form.html', 1000);
            }
        }, 3000);
    }
    
    showError(message) {
        this.container.innerHTML = `<pre style="color:red;">${message}</pre>`;
    }
    
    showMessage(message, color = 'black') {
        this.container.innerHTML = `<pre style="color:${color};">${message}</pre>`;
    }
    
    clear() {
        this.container.innerHTML = '';
    }
}

class AppController {
    constructor() {
        this.urlParams = new UrlParamsHandler();
        this.apiFetcher = new ApiFetcher();
        this.uiRenderer = new UIRenderer('container');
        this.redirectManager = new RedirectManager(this.uiRenderer);
        this.responseHandler = new ResponseHandler(
            this.redirectManager, 
            this.uiRenderer
        );
    }
    
    async init() {
        try {
            const data = await this.apiFetcher.fetchData('redirect.php');
            const targetUrl = this.responseHandler.handleResponse(data);
            
            if (!targetUrl) return;
            
            if (this.urlParams.getParam('forward') !== 'false') {
                this.redirectManager.redirect(targetUrl);
            } else {
                this.uiRenderer.renderIframe(targetUrl);
            }
        } catch (error) {
            this.uiRenderer.showError(error.message);
        }
    }
}

// Inicialización de la aplicación
window.onload = function() {
    console.log('Aplicación iniciada');
    const app = new AppController();
    app.init();
};