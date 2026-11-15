/**
 * Composable: useTableExport
 * Propósito: Proporciona utilidades globales para la exportación de registros tabulares
 * a formato Excel (CSV compatible con UTF-8 BOM) y generación de reportes impresos / PDF.
 *
 * Estándar SOGAC - UPTP:
 * - Compatible nativamente sin dependencias externas pesadas.
 * - Formato UTF-8 con BOM (\uFEFF) para correcta interpretación de acentos y caracteres especiales en Excel.
 * - Generador de vista de impresión enriquecida para descarga directa en PDF vía navegador.
 */

import type { Column } from '@/types/dataTable';

export function useTableExport() {
  /**
   * Exporta un conjunto de datos a archivo Excel/CSV con compatibilidad total UTF-8.
   */
  const exportToExcel = (
    data: any[],
    columns: Column[],
    filename: string = 'reporte-registros'
  ) => {
    if (!data || !data.length) {
      alert('No hay datos disponibles para exportar.');
      return;
    }

    // Filtrar columnas que no sean de acciones
    const exportCols = columns.filter(
      (c) => c.key !== 'actions' && c.key !== 'status_switch'
    );

    // Encabezados
    const headers = exportCols.map((c) => `"${(c.label || c.key).replace(/"/g, '""')}"`);

    // Filas
    const rows = data.map((row) => {
      return exportCols
        .map((col) => {
          let val = row[col.key];

          // Manejo de relaciones u objetos
          if (val === null || val === undefined) {
            val = '';
          } else if (typeof val === 'object') {
            val = val.nombre || val.nombre_loc || val.nombre_tpd || val.nombre_ano || JSON.stringify(val);
          } else if (typeof val === 'boolean') {
            val = val ? 'Activo' : 'Inactivo';
          }

          const strVal = String(val).replace(/"/g, '""');
          return `"${strVal}"`;
        })
        .join(';');
    });

    // Añadir BOM (\uFEFF) para que Excel detecte UTF-8 automáticamente
    const csvContent = '\uFEFF' + [headers.join(';'), ...rows].join('\r\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });

    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `${filename}-${new Date().toISOString().slice(0, 10)}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  /**
   * Genera una vista imprimible y lanza el diálogo del navegador para guardar en PDF o imprimir.
   */
  const exportToPdf = (
    title: string,
    columns: Column[],
    data: any[],
    subtitle?: string
  ) => {
    if (!data || !data.length) {
      alert('No hay datos disponibles para generar el reporte PDF.');
      return;
    }

    const exportCols = columns.filter(
      (c) => c.key !== 'actions' && c.key !== 'status_switch'
    );

    const printWindow = window.open('', '_blank', 'width=1100,height=800');
    if (!printWindow) {
      alert('Por favor permita las ventanas emergentes para generar el PDF.');
      return;
    }

    const fechaGeneracion = new Date().toLocaleDateString('es-VE', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });

    let html = `
      <!DOCTYPE html>
      <html lang="es">
      <head>
        <meta charset="UTF-8">
        <title>${title}</title>
        <style>
          @page {
            size: A4 landscape;
            margin: 15mm;
          }
          body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 20px;
            font-size: 12px;
          }
          .header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
          }
          .institution {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
          }
          .title {
            font-size: 18px;
            font-weight: 800;
            margin-top: 4px;
            color: #1e293b;
          }
          .subtitle {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
          }
          .meta {
            text-align: right;
            font-size: 10px;
            color: #64748b;
          }
          table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
          }
          th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            border-bottom: 2px solid #cbd5e1;
            text-align: left;
          }
          td {
            padding: 7px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
          }
          tr:nth-child(even) {
            background-color: #fbfcfe;
          }
          .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
          }
          .badge-active {
            background-color: #dcfce7;
            color: #15803d;
          }
          .badge-inactive {
            background-color: #fee2e2;
            color: #b91c1c;
          }
          .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
          }
          @media print {
            body { padding: 0; }
            button { display: none; }
          }
        </style>
      </head>
      <body>
        <div class="header">
          <div>
            <div class="institution">UNIVERSIDAD POLITÉCNICA TERRITORIAL DEL ESTADO PORTUGUESA "JJ MONTILLA"</div>
            <div class="title">${title}</div>
            ${subtitle ? `<div class="subtitle">${subtitle}</div>` : ''}
          </div>
          <div class="meta">
            <div><strong>SOGAC - Sistema de Gestión Académica</strong></div>
            <div>Fecha de emisión: ${fechaGeneracion}</div>
            <div>Total de registros: ${data.length}</div>
          </div>
        </div>

        <table>
          <thead>
            <tr>
              ${exportCols.map((c) => `<th>${c.label || c.key}</th>`).join('')}
            </tr>
          </thead>
          <tbody>
            ${data
              .map((row) => {
                return `<tr>${exportCols
                  .map((col) => {
                    let val = row[col.key];
                    if (val === null || val === undefined) {
                      val = '<span style="color:#94a3b8;">-</span>';
                    } else if (typeof val === 'object') {
                      val = val.nombre || val.nombre_loc || val.nombre_tpd || val.nombre_ano || JSON.stringify(val);
                    } else if (typeof val === 'boolean') {
                      val = val
                        ? '<span class="badge badge-active">Activo</span>'
                        : '<span class="badge badge-inactive">Inactivo</span>';
                    }
                    return `<td>${val}</td>`;
                  })
                  .join('')}</tr>`;
              })
              .join('')}
          </tbody>
        </table>

        <div class="footer">
          <div>Documento oficial expedido para control y auditoría institucional.</div>
          <div>Página 1 de 1</div>
        </div>

        <script>
          window.onload = function() {
            window.print();
          };
        </script>
      </body>
      </html>
    `;

    printWindow.document.open();
    printWindow.document.write(html);
    printWindow.document.close();
  };

  /**
   * Imprime la página actual usando la función estándar del navegador.
   */
  const printCurrentPage = () => {
    window.print();
  };

  return {
    exportToExcel,
    exportToPdf,
    printCurrentPage,
  };
}

