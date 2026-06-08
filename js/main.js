function getProjectBasePath() {
    const pathSegments = window.location.pathname.split('/').filter(Boolean);
    const projectIndex = pathSegments.indexOf('locket');

    return projectIndex >= 0
        ? `/${pathSegments.slice(0, projectIndex + 1).join('/')}`
        : '';
}

const API_BASE_URL = `${window.location.origin}${getProjectBasePath()}/control`;
const STORAGE_TOKEN_KEY = 'token';
const STORAGE_USER_KEY = 'userEmail';

function checkAuth() {
    const token = localStorage.getItem(STORAGE_TOKEN_KEY);
    const currentPage = window.location.pathname;

    if (!token && !currentPage.includes('login.html') && !currentPage.includes('index.html')) {
        window.location.href = '../login.html';
    }

    if (token && currentPage.includes('login.html')) {
        window.location.href = 'pages/dashboard.html';
    }
}

function logout() {
    if (confirm('Tem certeza que deseja sair?')) {
        localStorage.removeItem(STORAGE_TOKEN_KEY);
        localStorage.removeItem(STORAGE_USER_KEY);
        window.location.href = '../login.html';
    }
}

function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

function formatDate(date) {
    return new Intl.DateTimeFormat('pt-BR').format(new Date(date));
}

function formatDateTime(date) {
    return new Intl.DateTimeFormat('pt-BR', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    }).format(new Date(date));
}

function showToast(message, type = 'info') {
    const toastHTML = `
        <div class="toast show alert alert-${type} alert-dismissible fade" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);

    setTimeout(() => {
        const toast = toastContainer.querySelector('.toast');
        if (toast) toast.remove();
    }, 5000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
    document.body.appendChild(container);
    return container;
}

function debounce(func, delay) {
    let timeoutId;
    return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func(...args), delay);
    };
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidCNPJ(cnpj) {
    cnpj = cnpj.replace(/\D/g, '');
    return cnpj.length === 14;
}

function isValidCPF(cpf) {
    cpf = cpf.replace(/\D/g, '');
    return cpf.length === 11;
}

function isValidPhone(phone) {
    phone = phone.replace(/\D/g, '');
    return phone.length >= 10 && phone.length <= 11;
}

function maskInput(input, maskType) {
    input.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        
        switch(maskType) {
            case 'cpf':
                value = value.slice(0, 11);
                if (value.length > 3) value = value.slice(0, 3) + '.' + value.slice(3);
                if (value.length > 7) value = value.slice(0, 7) + '.' + value.slice(7);
                if (value.length > 11) value = value.slice(0, 14) + '-' + value.slice(11);
                break;
            case 'cnpj':
                value = value.slice(0, 14);
                if (value.length > 2) value = value.slice(0, 2) + '.' + value.slice(2);
                if (value.length > 6) value = value.slice(0, 6) + '.' + value.slice(6);
                if (value.length > 10) value = value.slice(0, 10) + '/' + value.slice(10);
                if (value.length > 15) value = value.slice(0, 18) + '-' + value.slice(14);
                break;
            case 'phone':
                value = value.slice(0, 11);
                if (value.length > 0) value = '(' + value;
                if (value.length > 3) value = value.slice(0, 3) + ') ' + value.slice(3);
                if (value.length > 10) value = value.slice(0, 10) + '-' + value.slice(10);
                break;
            case 'cep':
                value = value.slice(0, 8);
                if (value.length > 5) value = value.slice(0, 5) + '-' + value.slice(5);
                break;
        }
        
        this.value = value;
    });
}

function formDataToJSON(formData) {
    const json = {};
    for (let [key, value] of formData.entries()) {
        json[key] = value;
    }
    return json;
}

function exportToCSV(data, filename = 'export.csv') {
    if (data.length === 0) return;

    const headers = Object.keys(data[0]);
    let csv = headers.join(',') + '\n';

    data.forEach(row => {
        const values = headers.map(header => {
            const value = row[header];
            return typeof value === 'string' && value.includes(',') ? `"${value}"` : value;
        });
        csv += values.join(',') + '\n';
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
}

function printPage(title) {
    const printContent = document.querySelector('main').innerHTML;
    const printWindow = window.open('', '', 'width=900,height=600');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>${title}</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body>
            <h1>${title}</h1>
            ${printContent}
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

function showLoading() {
    const loader = document.createElement('div');
    loader.id = 'loadingOverlay';
    loader.innerHTML = `
        <div class="spinner-border text-primary" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;">
            <span class="visually-hidden">Carregando...</span>
        </div>
    `;
    document.body.appendChild(loader);
}

function hideLoading() {
    const loader = document.getElementById('loadingOverlay');
    if (loader) loader.remove();
}

function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

function initializePopovers() {
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

function initializePageData() {
    if (typeof loadUsers === 'function') {
        loadUsers();
    }

    if (typeof loadBusinessmen === 'function') {
        loadBusinessmen();
    }

    if (typeof loadAssets === 'function') {
        loadAssets();
    }

    if (typeof loadDashboardData === 'function') {
        loadDashboardData();
    }

    if (typeof loadAuditLogs === 'function') {
        loadAuditLogs();
    }
}

async function loadPythonAnalysis() {
    try {
        showLoading();
        const analysis = await pythonAnalysisAPI.completeAnalysis();
        hideLoading();
        return analysis;
    } catch (error) {
        hideLoading();
        console.error('Error loading Python analysis:', error);
        return null;
    }
}

async function displayLoans() {
    try {
        showLoading();
        const loans = await pythonAnalysisAPI.getLoans();
        hideLoading();
        return loans.data || [];
    } catch (error) {
        hideLoading();
        console.error('Error loading loans:', error);
        return [];
    }
}

async function displayOverdueLoans() {
    try {
        showLoading();
        const overdue = await pythonAnalysisAPI.getOverdueLoans();
        hideLoading();
        return overdue.data || [];
    } catch (error) {
        hideLoading();
        console.error('Error loading overdue loans:', error);
        return [];
    }
}

async function displayAlerts() {
    try {
        showLoading();
        const alerts = await pythonAnalysisAPI.getAlerts();
        hideLoading();
        return alerts.data || [];
    } catch (error) {
        hideLoading();
        console.error('Error loading alerts:', error);
        return [];
    }
}

document.addEventListener('DOMContentLoaded', function() {
    initializeTooltips();
    initializePopovers();
    initializePageData();

    const cpfInputs = document.querySelectorAll('[data-mask="cpf"]');
    cpfInputs.forEach(input => maskInput(input, 'cpf'));

    const cnpjInputs = document.querySelectorAll('[data-mask="cnpj"]');
    cnpjInputs.forEach(input => maskInput(input, 'cnpj'));

    const phoneInputs = document.querySelectorAll('[data-mask="phone"]');
    phoneInputs.forEach(input => maskInput(input, 'phone'));

    const cepInputs = document.querySelectorAll('[data-mask="cep"]');
    cepInputs.forEach(input => maskInput(input, 'cep'));
});
