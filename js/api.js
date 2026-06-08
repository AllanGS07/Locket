function getProjectBasePath() {
    const pathSegments = window.location.pathname.split('/').filter(Boolean);
    const projectIndex = pathSegments.indexOf('locket');

    return projectIndex >= 0
        ? `/${pathSegments.slice(0, projectIndex + 1).join('/')}`
        : '';
}

const API_BASE_URL = `${window.location.origin}${getProjectBasePath()}/control`;
const PYTHON_API_BASE_URL = 'http://localhost:5000/api';
const API_TIMEOUT = 10000;

function buildApiUrl(baseUrl, endpoint) {
    return `${baseUrl.replace(/\/$/, '')}${endpoint.startsWith('/') ? endpoint : `/${endpoint}`}`;
}

async function parseJsonResponse(response) {
    const text = await response.text();
    if (!text) {
        return null;
    }

    try {
        return JSON.parse(text);
    } catch (error) {
        console.warn('Resposta não foi um JSON válido:', error);
        return null;
    }
}

async function apiRequest(method, endpoint, data = null) {
    try {
        const url = buildApiUrl(API_BASE_URL, endpoint);
        const token = localStorage.getItem('token');

        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Authorization': token ? `Bearer ${token}` : ''
            }
        };

        if (data && (method === 'POST' || method === 'PUT')) {
            options.body = JSON.stringify(data);
        }

        const response = await Promise.race([
            fetch(url, options),
            new Promise((_, reject) =>
                setTimeout(() => reject(new Error('Timeout')), API_TIMEOUT)
            )
        ]);

        if (response.status === 401) {
            localStorage.removeItem('token');
            window.location.href = '../login.html';
            throw new Error('Sessão expirada. Por favor, faça login novamente.');
        }

        if (!response.ok) {
            const errorData = await parseJsonResponse(response);
            throw new Error(errorData?.message || `Erro ${response.status}: ${response.statusText}`);
        }

        return await parseJsonResponse(response);
    } catch (error) {
        console.error('API Error:', error);
        showToast(error.message, 'danger');
        throw error;
    }
}

async function pythonApiRequest(method, endpoint, data = null) {
    try {
        const url = buildApiUrl(PYTHON_API_BASE_URL, endpoint);

        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            }
        };

        if (data && (method === 'POST' || method === 'PUT')) {
            options.body = JSON.stringify(data);
        }

        const response = await Promise.race([
            fetch(url, options),
            new Promise((_, reject) =>
                setTimeout(() => reject(new Error('Timeout')), API_TIMEOUT)
            )
        ]);

        if (!response.ok) {
            const errorData = await parseJsonResponse(response);
            throw new Error(errorData?.message || `Erro ${response.status}: ${response.statusText}`);
        }

        return await parseJsonResponse(response);
    } catch (error) {
        console.error('Python API Error:', error);
        showToast(error.message, 'danger');
        throw error;
    }
}

const authAPI = {
    login: async (email, password) => {
        return apiRequest('POST', '/login', { email, password });
    },

    logout: async () => {
        localStorage.removeItem('token');
        return Promise.resolve();
    },

    refresh: async () => {
        return apiRequest('POST', '/refresh-token');
    }
};

const usersAPI = {
    getAll: async (page = 1, limit = 10) => {
        return apiRequest('GET', `/users?page=${page}&limit=${limit}`);
    },

    getById: async (id) => {
        return apiRequest('GET', `/users/${id}`);
    },

    create: async (data) => {
        return apiRequest('POST', '/users', data);
    },

    update: async (id, data) => {
        return apiRequest('PUT', `/users/${id}`, data);
    },

    delete: async (id) => {
        return apiRequest('DELETE', `/users/${id}`);
    },

    search: async (query) => {
        return apiRequest('GET', `/users/search?q=${query}`);
    }
};

const businessmenAPI = {
    getAll: async (page = 1, limit = 10) => {
        return apiRequest('GET', `/businessmen?page=${page}&limit=${limit}`);
    },

    getById: async (id) => {
        return apiRequest('GET', `/businessmen/${id}`);
    },

    create: async (data) => {
        return apiRequest('POST', '/businessmen', data);
    },

    update: async (id, data) => {
        return apiRequest('PUT', `/businessmen/${id}`, data);
    },

    delete: async (id) => {
        return apiRequest('DELETE', `/businessmen/${id}`);
    },

    search: async (query) => {
        return apiRequest('GET', `/businessmen/search?q=${query}`);
    }
};

const assetsAPI = {
    getAll: async (page = 1, limit = 10) => {
        return apiRequest('GET', `/assets?page=${page}&limit=${limit}`);
    },

    getById: async (id) => {
        return apiRequest('GET', `/assets/${id}`);
    },

    create: async (data) => {
        return apiRequest('POST', '/assets', data);
    },

    update: async (id, data) => {
        return apiRequest('PUT', `/assets/${id}`, data);
    },

    delete: async (id) => {
        return apiRequest('DELETE', `/assets/${id}`);
    },

    search: async (query) => {
        return apiRequest('GET', `/assets/search?q=${query}`);
    },

    getByBusinessman: async (businessmanId) => {
        return apiRequest('GET', `/assets/businessman/${businessmanId}`);
    }
};

const auditAPI = {
    getAll: async (page = 1, limit = 50) => {
        return apiRequest('GET', `/audit?page=${page}&limit=${limit}`);
    },

    getFiltered: async (filters) => {
        const query = new URLSearchParams(filters).toString();
        return apiRequest('GET', `/audit?${query}`);
    },

    getStats: async () => {
        return apiRequest('GET', '/audit/stats');
    }
};

const reportsAPI = {
    generateLoans: async (format = 'pdf') => {
        return apiRequest('GET', `/reports/loans?format=${format}`);
    },

    generateAssets: async (format = 'pdf') => {
        return apiRequest('GET', `/reports/assets?format=${format}`);
    },

    generateUsers: async (format = 'pdf') => {
        return apiRequest('GET', `/reports/users?format=${format}`);
    },

    generateBusinessmen: async (format = 'pdf') => {
        return apiRequest('GET', `/reports/businessmen?format=${format}`);
    },

    generateFinancial: async (format = 'pdf') => {
        return apiRequest('GET', `/reports/financial?format=${format}`);
    },

    generateAudit: async (format = 'pdf') => {
        return apiRequest('GET', `/reports/audit?format=${format}`);
    },

    generateCustom: async (data) => {
        return apiRequest('POST', '/reports/custom', data);
    }
};

const dashboardAPI = {
    getStats: async () => {
        return apiRequest('GET', '/dashboard/stats');
    },

    getRecentActivity: async (limit = 10) => {
        return apiRequest('GET', `/dashboard/activities?limit=${limit}`);
    }
};

const pythonAnalysisApi = {
    getLoans: async () => pythonApiRequest('GET', '/emprestimos'),
    getOverdueLoans: async () => pythonApiRequest('GET', '/emprestimos/atrasados'),
    getLoansSummary: async () => pythonApiRequest('GET', '/emprestimos/resumo'),
    getUserStatistics: async () => pythonApiRequest('GET', '/usuarios/estatisticas'),
    getMostBorrowedItems: async (limit = 10) => pythonApiRequest('GET', `/objetos/mais-emprestados?limite=${limit}`),
    getAlerts: async () => pythonApiRequest('GET', '/alertas'),
    getCriticalAlerts: async () => pythonApiRequest('GET', '/alertas/criticos'),
    predictLoanDelay: async (data) => pythonApiRequest('POST', '/predicoes/emprestimo', data),
    trainModel: async () => pythonApiRequest('POST', '/modelo/treinar'),
    generateJsonReport: async () => pythonApiRequest('GET', '/relatorios/json'),
    generateCsvReport: async () => pythonApiRequest('GET', '/relatorios/csv'),
    generatePredictionsReport: async () => pythonApiRequest('GET', '/relatorios/previsoes'),
    completeAnalysis: async () => pythonApiRequest('GET', '/analise/completa'),
    getStatus: async () => pythonApiRequest('GET', '/status'),
    getHealth: async () => pythonApiRequest('GET', '/saude'),
    clearCache: async () => pythonApiRequest('POST', '/cache/limpar'),

    obterEmprestimos: async () => pythonAnalysisApi.getLoans(),
    obterEmprestimosAtrasados: async () => pythonAnalysisApi.getOverdueLoans(),
    obterResumoEmprestimos: async () => pythonAnalysisApi.getLoansSummary(),
    obterEstatisticasUsuarios: async () => pythonAnalysisApi.getUserStatistics(),
    obterObjetos_mais_emprestados: async (limite = 10) => pythonAnalysisApi.getMostBorrowedItems(limite),
    obterAlertas: async () => pythonAnalysisApi.getAlerts(),
    obterAlertasCriticos: async () => pythonAnalysisApi.getCriticalAlerts(),
    preverAtrasoEmprestimo: async (dados) => pythonAnalysisApi.predictLoanDelay(dados),
    treinarModelo: async () => pythonAnalysisApi.trainModel(),
    gerarRelatorioJson: async () => pythonAnalysisApi.generateJsonReport(),
    gerarRelatorioCsv: async () => pythonAnalysisApi.generateCsvReport(),
    gerarRelatorioPrevisoes: async () => pythonAnalysisApi.generatePredictionsReport(),
    analiseCompleta: async () => pythonAnalysisApi.completeAnalysis(),
    obterStatus: async () => pythonAnalysisApi.getStatus(),
    obterSaude: async () => pythonAnalysisApi.getHealth(),
    limparCache: async () => pythonAnalysisApi.clearCache()
};

const pythonAnalysisAPI = pythonAnalysisApi;

const notificationAPI = {
    getAll: async () => {
        return apiRequest('GET', '/notifications');
    },

    markAsRead: async (id) => {
        return apiRequest('PUT', `/notifications/${id}`, { read: true });
    },

    markAllAsRead: async () => {
        return apiRequest('PUT', '/notifications/mark-all-read');
    },

    delete: async (id) => {
        return apiRequest('DELETE', `/notifications/${id}`);
    }
};

const profileAPI = {
    getProfile: async () => {
        return apiRequest('GET', '/profile');
    },

    updateProfile: async (data) => {
        return apiRequest('PUT', '/profile', data);
    },

    changePassword: async (currentPassword, newPassword) => {
        return apiRequest('PUT', '/profile/password', {
            currentPassword,
            newPassword
        });
    },

    getActivityLog: async () => {
        return apiRequest('GET', '/profile/activity');
    }
};

function downloadFile(url, filename) {
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

async function uploadFile(endpoint, file) {
    try {
        const formData = new FormData();
        formData.append('file', file);

        const token = localStorage.getItem('token');
        const url = buildApiUrl(API_BASE_URL, endpoint);
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Authorization': token ? `Bearer ${token}` : ''
            },
            body: formData
        });

        if (!response.ok) {
            throw new Error(`Upload failed: ${response.statusText}`);
        }

        return await parseJsonResponse(response);
    } catch (error) {
        console.error('Upload Error:', error);
        showToast(error.message, 'danger');
        throw error;
    }
}

async function testAPIConnection() {
    try {
        const response = await apiRequest('GET', '/health');
        console.log('API Connection: OK', response);
        return true;
    } catch (error) {
        console.error('API Connection: FAILED', error);
        showToast('Não foi possível conectar à API. Verifique sua conexão.', 'warning');
        return false;
    }
}

async function testPythonAPIConnection() {
    try {
        const response = await pythonApiRequest('GET', '/saude');
        console.log('Python API Connection: OK', response);
        return true;
    } catch (error) {
        console.error('Python API Connection: FAILED', error);
        return false;
    }
}

window.apiRequest = apiRequest;
window.pythonApiRequest = pythonApiRequest;
window.authAPI = authAPI;
window.usersAPI = usersAPI;
window.businessmenAPI = businessmenAPI;
window.assetsAPI = assetsAPI;
window.auditAPI = auditAPI;
window.reportsAPI = reportsAPI;
window.dashboardAPI = dashboardAPI;
window.pythonAnalysisApi = pythonAnalysisApi;
window.pythonAnalysisAPI = pythonAnalysisAPI;
window.notificationAPI = notificationAPI;
window.profileAPI = profileAPI;
window.uploadFile = uploadFile;
window.downloadFile = downloadFile;
window.testAPIConnection = testAPIConnection;
window.testPythonAPIConnection = testPythonAPIConnection;
