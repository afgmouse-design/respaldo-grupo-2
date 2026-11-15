// types/dataTable.ts
export interface Column {
  key: string;           // nombre del campo en el objeto
  label: string;         // texto a mostrar en el encabezado
  sortable?: boolean;    // si se puede ordenar (opcional)
  width?: string;        // clase Tailwind o estilo
  maxWidth?: string;     // clase Tailwind o estilo
  align?: 'left' | 'center' | 'right';
  // Slot personalizado: si se necesita renderizado complejo, se usará un slot con el nombre de la columna
}

export interface FilterConfig {
  type: 'search' | 'select' | 'dateRange';
  key: string;            // clave del filtro en la query string
  label?: string;
  options?: { value: string; label: string }[]; // para select
  placeholder?: string;
}