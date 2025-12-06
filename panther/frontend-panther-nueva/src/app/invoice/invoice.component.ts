// src/app/invoice/invoice.component.ts
import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
    selector: 'app-invoice',
    standalone: true,
    imports: [CommonModule, FormsModule],
    templateUrl: './invoice.component.html',
    styleUrls: ['./invoice.component.scss']
})
export class InvoiceComponent {
    invoiceData: any = {
        NumeroFactura: '',
        Fecha: '',
        Cliente: {
            Nombre: '',
            Documento: ''
        },
        Items: [
            {
                Descripcion: '',
                Cantidad: 1,
                ValorUnitario: 0
            }
        ]
    };

    enviarFactura() {
        // calcular base y total con IVA
        const base = this.invoiceData.Items[0].Cantidad * this.invoiceData.Items[0].ValorUnitario;
        const iva = base * 0.19;
        const total = base + iva;

        // construir payload simulado
        const body = {
            nit: '12345',
            numeroResolucion: '18760000001',
            consecutivoDcto: this.invoiceData.NumeroFactura,
            prefijoDcto: 'SETP',
            fechaVencimiento: this.invoiceData.Fecha,
            formaPago: '1',
            mediosPago: ['10'],
            total: total,
            cliente: {
                nombreRazonSocial: this.invoiceData.Cliente.Nombre,
                tipoIdentificacion: 'CC',
                numIdentificacion: this.invoiceData.Cliente.Documento,
                naturaleza: 'NATURAL',
                email: 'demo@clarisa.co',
                direccion: 'Cra 1 # 1-1',
                ciudad: '15001',
                telefono: '3000000000'
            },
            items: [
                {
                    codigo: '001',
                    nombreItem: this.invoiceData.Items[0].Descripcion,
                    precioBaseUnitario: this.invoiceData.Items[0].ValorUnitario,
                    cantidad: this.invoiceData.Items[0].Cantidad,
                    unidad: '94',
                    impuestos: [
                        { tipo: 'IVA', claseImpuesto: 'PO', tarifaTributo: 19 }
                    ]
                }
            ]
        };
        // construir string bonito para el alert
        const facturaBonita = `
            ✅ Factura simulada creada

            📄 Número: ${body.consecutivoDcto}
            📅 Fecha vencimiento: ${body.fechaVencimiento}

            👤 Cliente:
            - Nombre: ${body.cliente.nombreRazonSocial}
            - Documento: ${body.cliente.tipoIdentificacion} ${body.cliente.numIdentificacion}
            - Email: ${body.cliente.email}
            - Dirección: ${body.cliente.direccion}
            - Ciudad: ${body.cliente.ciudad}
            - Teléfono: ${body.cliente.telefono}

            🛒 Item:
            - Código: ${body.items[0].codigo}
            - Descripción: ${body.items[0].nombreItem}
            - Cantidad: ${body.items[0].cantidad}
            - Valor Unitario: $${body.items[0].precioBaseUnitario}
            - IVA: ${body.items[0].impuestos[0].tarifaTributo}%

            💰 Total: $${body.total}
            `;

        alert(facturaBonita);
        // mostrar resultado en consola y alerta
        console.log('Factura simulada:', body);
        alert('✅ Factura simulada creada:\n' + JSON.stringify(body, null, 2));
    }
}
