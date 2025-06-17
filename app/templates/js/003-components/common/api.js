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

    try {
      const response = await fetch(url, options);

      if (!response.ok) {
        let errorText = await response.text();

        throw new Error(`HTTP error ${response.status}: ${errorText}`);
      }

      const contentType = response.headers.get('Content-Type') || '';

      return contentType.includes('application/json')
        ? await response.json()
        : await response.text();
    } catch (error) {
      console.error('API request failed:', error);

      throw error;
    }
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
