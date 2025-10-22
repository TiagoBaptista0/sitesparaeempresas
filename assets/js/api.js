// API Helper Functions
async function apiCall(endpoint, data = {}) {
    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(data)
        });

        return await response.json();
    } catch (error) {
        console.error('Erro na API:', error);
        return { success: false, error: 'Erro de conexão' };
    }
}