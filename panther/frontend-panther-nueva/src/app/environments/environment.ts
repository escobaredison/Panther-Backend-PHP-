// src/environments/environment.ts
export const environment = {
    production: false,

    // Tu backend Panther (si lo sigues usando)
    authApi: 'http://localhost/backend_panther/panther/rest/',
    pantherApi: 'http://localhost/backend_panther/panther/rest/',

    // Clarisa APIs (login y factura)
    clarisaLoginUrl: 'https://pru.clarisacloud.com:8443/seguridad/rest/api/v1/login/',
    clarisaInvoiceUrl: 'https://pru.clarisacloud.com:8443/api/factura/rest/v1/factura/nacional'
};
