/* ===================================
   LockedIn - Main JavaScript
   =================================== */

// Configuration
const API_BASE_URL = 'http://localhost:3000/api'; // Change to your API URL
const STORAGE_TOKEN_KEY = 'token';
const STORAGE_USER_KEY = 'userEmail';

// Check authentication on page load
function checkAuth() {
    const token = localStorage.getItem(STORAGE_TOKEN_KEY);
    const currentPage = window.location.pathname;

    // If not authenticated and trying to access protected pages
    if (!token && !currentPage.includes('login.html') && !currentPage.includes('index.html')) {
        window.location.href = '../login.html';
    }

    // If authenticated and trying to access login page, redirect to dashboard
    if (token && currentPage.includes('login.html')) {
        window.location.href = 'pages/dashboard.html';
    }
}

// Logout function
function logout() {
    if (confirm('Tem certeza que deseja sair?')) {
        localStorage.removeItem(STORAGE_TOKEN_KEY);
        localStorage.removeItem(STORAGE_USER_KEY);
        window.location.href = '../login.html';
    }
}

// Format currency
function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

// Format date
function formatDate(date) {
    return new Intl.DateTimeFormat('pt-BR').format(new Date(date));
}

// Format date with time
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

// Show toast notification
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

// Debounce function for search
function debounce(func, delay) {
    let timeoutId;
    return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func(...args), delay);
    };
}

// Validate email
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Validate CNPJ
function isValidCNPJ(cnpj) {
    cnpj = cnpj.replace(/\D/g, '');
    return cnpj.length === 14;
}

// Validate CPF
function isValidCPF(cpf) {
    cpf = cpf.replace(/\D/g, '');
    return cpf.length === 11;
}

// Validate phone
function isValidPhone(phone) {
    phone = phone.replace(/\D/g, '');
    return phone.length >= 10 && phone.length <= 11;
}

// Mask input field
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

// Convert FormData to JSON
function formDataToJSON(formData) {
    const json = {};
    for (let [key, value] of formData.entries()) {
        json[key] = value;
    }
    return json;
}

// Export as CSV
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

// Print page
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

// Page loading animation
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

// Initialize tooltips (Bootstrap)
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Initialize popovers (Bootstrap)
function initializePopovers() {
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

// Document ready
document.addEventListener('DOMContentLoaded', function() {
    initializeTooltips();
    initializePopovers();

    // Apply input masks
    const cpfInputs = document.querySelectorAll('[data-mask="cpf"]');
    cpfInputs.forEach(input => maskInput(input, 'cpf'));

    const cnpjInputs = document.querySelectorAll('[data-mask="cnpj"]');
    cnpjInputs.forEach(input => maskInput(input, 'cnpj'));

    const phoneInputs = document.querySelectorAll('[data-mask="phone"]');
    phoneInputs.forEach(input => maskInput(input, 'phone'));

    const cepInputs = document.querySelectorAll('[data-mask="cep"]');
    cepInputs.forEach(input => maskInput(input, 'cep'));
});
