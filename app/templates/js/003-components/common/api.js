class Api {
  constructor(baseUrl = null) {
    this.baseUrl = baseUrl || `${window.location.origin}`;
  }

  async request(endpoint, method, data = null, customHeaders = {}) {
    const url = `${this.baseUrl}${endpoint}`;

    const headers = {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      ...customHeaders,
    };

    const options = {
      method,
      headers,
    };

    if (data && method !== 'GET') {
      options.body = JSON.stringify(data);
    }

    const response = await fetch(url, options);
    const contentType = response.headers.get('Content-Type') || '';
    const isJson = contentType.includes('application/json');

    if (!response.ok) {
      const result = isJson ? await response.json() : await response.text();
      const errorText = isJson ? result.errors || result.error || 'Неизвестная ошибка' : result;

      throw errorText;
    }

    return isJson ? await response.json() : await response.text();
  }

  async get(endpoint, params = {}, headers = {}) {
    const query = new URLSearchParams(params).toString();
    const fullEndpoint = query ? `${endpoint}?${query}` : endpoint;

    return this.request(fullEndpoint, 'GET', null, headers);
  }

  async post(endpoint, data = {}, headers = {}) {
    return this.request(endpoint, 'POST', data, headers);
  }

  async put(endpoint, data = {}, headers = {}) {
    return this.request(endpoint, 'PUT', data, headers);
  }

  async patch(endpoint, data = {}, headers = {}) {
    return this.request(endpoint, 'PATCH', data, headers);
  }

  async delete(endpoint, headers = {}) {
    return this.request(endpoint, 'DELETE', null, headers);
  }
}

export default Api;
