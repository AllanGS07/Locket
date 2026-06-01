const API_BASE_URL = 'http://localhost:3000/api';
const PYTHON_API_BASE_URL = 'http://localhost:5000/api';
const API_TIMEOUT = 10000;

async function apiRequest(method, endpoint, data = null) {
    try {
        const url = `${API_BASE_URL}${endpoint}`;
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
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || `Erro ${response.status}: ${response.statusText}`);
        }

        return await response.json();
    } catch (error) {
        console.error('API Error:', error);
        showToast(error.message, 'danger');
        throw error;
    }
}

async function pythonApiRequest(method, endpoint, data = null) {
    try {
        const url = `${PYTHON_API_BASE_URL}${endpoint}`;

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
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || `Erro ${response.status}: ${response.statusText}`);
        }

        return await response.json();
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

const pythonAnalysisAPI = {
    obterEmprestimos: async () => {
        return pythonApiRequest('GET', '/emprestimos');
    },

    obterEmprestimosAtrasados: async () => {
        return pythonApiRequest('GET', '/emprestimos/atrasados');
    },

    obterResumoEmprestimos: async () => {
        return pythonApiRequest('GET', '/emprestimos/resumo');
    },

    obterEstatisticasUsuarios: async () => {
        return pythonApiRequest('GET', '/usuarios/estatisticas');
    },

    obterObjetos_mais_emprestados: async (limite = 10) => {
        return pythonApiRequest('GET', `/objetos/mais-emprestados?limite=${limite}`);
    },

    obterAlertas: async () => {
        return pythonApiRequest('GET', '/alertas');
    },

    obterAlertasCriticos: async () => {
        return pythonApiRequest('GET', '/alertas/criticos');
    },

    preverAtrasoEmprestimo: async (dados) => {
        return pythonApiRequest('POST', '/predicoes/emprestimo', dados);
    },

    treinarModelo: async () => {
        return pythonApiRequest('POST', '/modelo/treinar');
    },

    gerarRelatorioJson: async () => {
        return pythonApiRequest('GET', '/relatorios/json');
    },

    gerarRelatorioCsv: async () => {
        return pythonApiRequest('GET', '/relatorios/csv');
    },

    gerarRelatorioPrevisoes: async () => {
        return pythonApiRequest('GET', '/relatorios/previsoes');
    },

    analiseCompleta: async () => {
        return pythonApiRequest('GET', '/analise/completa');
    },

    obterStatus: async () => {
        return pythonApiRequest('GET', '/status');
    },

    obterSaude: async () => {
        return pythonApiRequest('GET', '/saude');
    },

    limparCache: async () => {
        return pythonApiRequest('POST', '/cache/limpar');
    }
};

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
        const response = await fetch(`${API_BASE_URL}${endpoint}`, {
            method: 'POST',
            headers: {
                'Authorization': token ? `Bearer ${token}` : ''
            },
            body: formData
        });

        if (!response.ok) {
            throw new Error(`Upload failed: ${response.statusText}`);
        }

        return await response.json();
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
window.pythonAnalysisAPI = pythonAnalysisAPI;
window.notificationAPI = notificationAPI;
window.profileAPI = profileAPI;
window.uploadFile = uploadFile;
window.downloadFile = downloadFile;
window.testAPIConnection = testAPIConnection;
window.testPythonAPIConnection = testPythonAPIConnection;

window.pythonAnalysisAPI.getLoans = pythonAnalysisAPI.obterEmprestimos;
window.pythonAnalysisAPI.getOverdueLoans = pythonAnalysisAPI.obterEmprestimosAtrasados;
window.pythonAnalysisAPI.getLoansSummary = pythonAnalysisAPI.obterResumoEmprestimos;
window.pythonAnalysisAPI.getUserStatistics = pythonAnalysisAPI.obterEstatisticasUsuarios;
window.pythonAnalysisAPI.getMostBorrowedItems = pythonAnalysisAPI.obterObjetos_mais_emprestados;
window.pythonAnalysisAPI.getAlerts = pythonAnalysisAPI.obterAlertas;
window.pythonAnalysisAPI.getCriticalAlerts = pythonAnalysisAPI.obterAlertasCriticos;
window.pythonAnalysisAPI.predictLoanDelay = pythonAnalysisAPI.preverAtrasoEmprestimo;
window.pythonAnalysisAPI.trainModel = pythonAnalysisAPI.treinarModelo;
window.pythonAnalysisAPI.generateJsonReport = pythonAnalysisAPI.gerarRelatorioJson;
window.pythonAnalysisAPI.generateCsvReport = pythonAnalysisAPI.gerarRelatorioCsv;
window.pythonAnalysisAPI.generatePredictionsReport = pythonAnalysisAPI.gerarRelatorioPrevisoes;
window.pythonAnalysisAPI.completeAnalysis = pythonAnalysisAPI.analiseCompleta;
window.pythonAnalysisAPI.getStatus = pythonAnalysisAPI.obterStatus;
