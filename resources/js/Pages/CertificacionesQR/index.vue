<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import Badge from '@/components/ui/badge/Badge.vue';
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
  CardDescription,
} from '@/components/ui/card';
import {
  QrCode,
  ShieldCheck,
  ShieldAlert,
  FileCheck,
  Lock,
  Search,
  ExternalLink,
  Plus,
  RefreshCw,
  Award,
  History,
  CheckCircle2,
  AlertTriangle,
  FileText,
  Copy,
  Printer,
  Sparkles,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: route('dashboard') },
  { title: 'Certificaciones y Validación', href: route('certificaciones.qr') },
  { title: 'Validación Digital con Código QR', href: route('certificaciones.qr') },
];

const tabActiva = ref<'documentos' | 'titulos' | 'eventos'>('documentos');

// Documentos certificados con QR emitidos (qr_validated_generated)
const certificados = ref([
  {
    qvg_codigo: 101,
    qvg_unique_id: 'DOC-2026-9B38F7A1',
    qvg_pdf_name: 'Constancia_Estudio_30123456_2026_I.pdf',
    estudiante: 'Alejandro José González Pérez',
    cedula: '30123456',
    programa: 'PNF en Informática',
    tipo: 'Constancia de Estudios',
    qvg_verification_hash: 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
    qvg_created_at: '2026-09-20 10:14:22',
    qvg_status: 'VALIDO',
    qvg_revoked_at: null,
    generador: 'Lic. Ana Gómez (DACE)',
  },
  {
    qvg_codigo: 102,
    qvg_unique_id: 'DOC-2026-44D1E89C',
    qvg_pdf_name: 'Notas_Certificadas_28456789.pdf',
    estudiante: 'Mariana Victoria Rivas Delgado',
    cedula: '28456789',
    programa: 'PNF en Administración',
    tipo: 'Notas Certificadas',
    qvg_verification_hash: '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08',
    qvg_created_at: '2026-09-18 14:32:05',
    qvg_status: 'VALIDO',
    qvg_revoked_at: null,
    generador: 'Lic. Ana Gómez (DACE)',
  },
  {
    qvg_codigo: 103,
    qvg_unique_id: 'DOC-2026-11A0E992',
    qvg_pdf_name: 'Carta_Culminacion_29876543.pdf',
    estudiante: 'Gabriel Antonio Herrera Castillo',
    cedula: '29876543',
    programa: 'PNF en Informática',
    tipo: 'Carta de Culminación',
    qvg_verification_hash: '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8',
    qvg_created_at: '2026-09-10 09:20:11',
    qvg_status: 'REVOCADO',
    qvg_revoked_at: '2026-09-12 11:00:00',
    generador: 'Prof. Carlos Mendoza (Sec.)',
  },
]);

// Títulos universitarios registrados con QR Criptográfico (qr_validated_generated_titulo)
const titulosQR = ref([
  {
    qgt_codigo: 201,
    qgt_unique_id: 'TIT-UPTP-2026-INF-001',
    qgt_pdf_name: 'Titulo_Ingeniero_30123456.pdf',
    graduando: 'Alejandro José González Pérez',
    cedula: '30123456',
    titulo: 'Ingeniero en Informática',
    mencion: 'Summa Cum Laude (19.82)',
    tomo_folio: 'TOMO XXIV / FOLIO 0142',
    qgt_verification_hash: 'ca978112ca1bbdcafac231b39a23dc4da786eff8147c4e72b9807785afee48bb',
    qgt_created_at: '2026-09-24 16:45:00',
    qgt_status: 'VALIDO',
    qgt_revoked_at: null,
  },
  {
    qgt_codigo: 202,
    qgt_unique_id: 'TIT-UPTP-2026-ADM-002',
    qgt_pdf_name: 'Titulo_Licenciada_28456789.pdf',
    graduando: 'Mariana Victoria Rivas Delgado',
    cedula: '28456789',
    titulo: 'Licenciada en Administración',
    mencion: 'Magna Cum Laude (19.45)',
    tomo_folio: 'TOMO XXIV / FOLIO 0143',
    qgt_verification_hash: '4e07408562bedb8b60ce05c1decfe3ad16b72230967de01f640b7e4729b49fce',
    qgt_created_at: '2026-09-24 17:00:00',
    qgt_status: 'VALIDO',
    qgt_revoked_at: null,
  },
]);

// Bitácora de eventos de auditoría (eventos)
const eventosAuditoria = ref([
  {
    eve_codigo: 501,
    eve_usu_nombre: 'anagomez_dace',
    eve_formulario: 'CertificacionesQR',
    eve_tipo_evento: 'QR_GENERATE',
    eve_fecha: '2026-09-24 17:00:00',
    eve_ip: '192.168.10.45',
    eve_observacion: 'Emisión de QR Digital para Título Licenciatura C.I. 28456789 con hash SHA-256',
  },
  {
    eve_codigo: 502,
    eve_usu_nombre: 'carlos_sec',
    eve_formulario: 'CertificacionesQR',
    eve_tipo_evento: 'QR_REVOKE',
    eve_fecha: '2026-09-12 11:00:00',
    eve_ip: '192.168.10.12',
    eve_observacion: 'Revocación de Carta de Culminación DOC-2026-11A0E992 por corrección de promedio',
  },
  {
    eve_codigo: 503,
    eve_usu_nombre: 'secretaria_general',
    eve_formulario: 'ProtocoloGrados',
    eve_tipo_evento: 'QR_GENERATE',
    eve_fecha: '2026-09-24 16:45:00',
    eve_ip: '192.168.10.02',
    eve_observacion: 'Generación de QR de Grado para Ingeniero en Informática C.I. 30123456',
  },
]);

// Verificador interactivo de QR en línea
const inputHashBusqueda = ref('');
const resultadoValidacion = ref<any>(null);

const verificarHash = () => {
  if (!inputHashBusqueda.value.trim()) return;

  const query = inputHashBusqueda.value.trim();
  const doc = certificados.value.find(c => c.qvg_unique_id === query || c.qvg_verification_hash.includes(query) || c.cedula === query);
  const tit = titulosQR.value.find(t => t.qgt_unique_id === query || t.qgt_verification_hash.includes(query) || t.cedula === query);

  if (doc) {
    resultadoValidacion.value = {
      tipo: 'DOCUMENTO',
      identificador: doc.qvg_unique_id,
      nombre: doc.qvg_pdf_name,
      estudiante: doc.estudiante,
      cedula: doc.cedula,
      hash: doc.qvg_verification_hash,
      fecha: doc.qvg_created_at,
      status: doc.qvg_status,
      valido: doc.qvg_status === 'VALIDO',
    };
  } else if (tit) {
    resultadoValidacion.value = {
      tipo: 'TÍTULO UNIVERSITARIO',
      identificador: tit.qgt_unique_id,
      nombre: tit.titulo,
      estudiante: tit.graduando,
      cedula: tit.cedula,
      hash: tit.qgt_verification_hash,
      fecha: tit.qgt_created_at,
      status: tit.qgt_status,
      valido: tit.qgt_status === 'VALIDO',
    };
  } else {
    resultadoValidacion.value = {
      noEncontrado: true,
      query,
    };
  }
};

// Modal Emisión
const modalEmision = ref(false);
const formEmision = ref({
  cedula: '30123456',
  tipo: 'Constancia de Buena Conducta',
  programa: 'PNF en Informática',
});

const emitirDocumento = () => {
  const hashGen = Array.from({ length: 64 }, () => Math.floor(Math.random() * 16).toString(16)).join('');
  const uniqueId = `DOC-2026-${Math.random().toString(36).substring(2, 8).toUpperCase()}`;

  certificados.value.unshift({
    qvg_codigo: Date.now(),
    qvg_unique_id: uniqueId,
    qvg_pdf_name: `${formEmision.value.tipo.replace(/\s+/g, '_')}_${formEmision.value.cedula}.pdf`,
    estudiante: 'Alejandro José González Pérez',
    cedula: formEmision.value.cedula,
    programa: formEmision.value.programa,
    tipo: formEmision.value.tipo,
    qvg_verification_hash: hashGen,
    qvg_created_at: new Date().toISOString().replace('T', ' ').substring(0, 19),
    qvg_status: 'VALIDO',
    qvg_revoked_at: null,
    generador: 'Usuario Actual (DACE)',
  });

  eventosAuditoria.value.unshift({
    eve_codigo: Date.now(),
    eve_usu_nombre: 'usuario_sesion',
    eve_formulario: 'CertificacionesQR',
    eve_tipo_evento: 'QR_GENERATE',
    eve_fecha: new Date().toISOString().replace('T', ' ').substring(0, 19),
    eve_ip: '192.168.10.88',
    eve_observacion: `Generación de ${formEmision.value.tipo} (${uniqueId}) para C.I. ${formEmision.value.cedula}`,
  });

  modalEmision.value = false;
};
</script>

<template>
  <Head title="Validación y Certificación Digital QR - SOGAC" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 sm:p-6 space-y-6">

      <!-- HEADER -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
        <div>
          <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-900/50 mb-2">
            <QrCode class="h-3.5 w-3.5" />
            Acreditación Criptográfica y Validación Documental
          </div>
          <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
            Certificaciones y Validación Digital QR
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 max-w-3xl">
            Emisión de constancias, certificaciones académicas y títulos con código QR de verificación instantánea y firma criptográfica SHA-256 inmutable.
          </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
          <Button size="sm" class="bg-red-600 hover:bg-red-700 text-white gap-1.5" @click="modalEmision = true">
            <Plus class="h-4 w-4" />
            Emitir Documento con QR
          </Button>
        </div>
      </div>

      <!-- SCANNER / VERIFICADOR DIGITAL RÁPIDO DE CÓDIGO QR -->
      <Card class="border-indigo-200 dark:border-indigo-900/50 bg-gradient-to-br from-indigo-50/40 via-white to-indigo-100/30 dark:from-slate-950 dark:via-indigo-950/20 dark:to-slate-900">
        <CardContent class="p-5 space-y-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-indigo-700 dark:text-indigo-400 font-bold text-sm">
              <ShieldCheck class="h-5 w-5" />
              Verificador Público de Autenticidad Criptográfica
            </div>
            <Badge variant="outline" class="border-indigo-300 text-indigo-700 dark:border-indigo-800 dark:text-indigo-300">
              SHA-256 Integrity Check
            </Badge>
          </div>

          <div class="flex flex-col sm:flex-row gap-2">
            <div class="relative flex-1">
              <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
              <input
                v-model="inputHashBusqueda"
                type="text"
                placeholder="Pegue el Hash SHA-256, ID Único (DOC-2026-...) o Cédula de Identidad..."
                class="w-full pl-9 pr-4 py-2 text-xs sm:text-sm rounded-lg border border-indigo-200 dark:border-indigo-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                @keyup.enter="verificarHash"
              />
            </div>
            <Button size="sm" class="bg-indigo-600 hover:bg-indigo-700 text-white gap-1.5" @click="verificarHash">
              <ShieldCheck class="h-4 w-4" />
              Verificar Hash
            </Button>
          </div>

          <!-- RESULTADO DE VALIDACIÓN -->
          <div v-if="resultadoValidacion" class="mt-3 p-4 rounded-xl border" :class="resultadoValidacion.valido ? 'bg-emerald-50/80 border-emerald-300 dark:bg-emerald-950/30 dark:border-emerald-800' : 'bg-red-50/80 border-red-300 dark:bg-red-950/30 dark:border-red-800'">
            <div v-if="resultadoValidacion.noEncontrado" class="flex items-center gap-2 text-red-700 dark:text-red-300 text-xs font-semibold">
              <ShieldAlert class="h-5 w-5 text-red-600 shrink-0" />
              No se encontró ningún registro ni certificado asociado al token "{{ resultadoValidacion.query }}". Verifique el código QR escaneado.
            </div>

            <div v-else class="space-y-2 text-xs">
              <div class="flex items-center justify-between">
                <span class="font-bold flex items-center gap-1.5" :class="resultadoValidacion.valido ? 'text-emerald-800 dark:text-emerald-300' : 'text-red-800 dark:text-red-300'">
                  <CheckCircle2 v-if="resultadoValidacion.valido" class="h-4 w-4 text-emerald-600" />
                  <AlertTriangle v-else class="h-4 w-4 text-red-600" />
                  {{ resultadoValidacion.valido ? 'DOCUMENTO AUTÉNTICO Y VÁLIDO' : 'DOCUMENTO REVOCADO O INVÁLIDO' }}
                </span>
                <Badge :variant="resultadoValidacion.valido ? 'successCustom' : 'destructiveCustom'">
                  {{ resultadoValidacion.status }}
                </Badge>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                <div>
                  <span class="text-slate-400 block">Tipo & ID:</span>
                  <span class="font-bold text-slate-800 dark:text-slate-200">{{ resultadoValidacion.tipo }} ({{ resultadoValidacion.identificador }})</span>
                </div>
                <div>
                  <span class="text-slate-400 block">Titular:</span>
                  <span class="font-bold text-slate-800 dark:text-slate-200">{{ resultadoValidacion.estudiante }} (C.I. {{ resultadoValidacion.cedula }})</span>
                </div>
                <div>
                  <span class="text-slate-400 block">Fecha Emisión:</span>
                  <span class="font-mono text-slate-700 dark:text-slate-300">{{ resultadoValidacion.fecha }}</span>
                </div>
              </div>

              <div class="pt-1">
                <span class="text-slate-400 block">Firma Hash Criptográfica SHA-256:</span>
                <span class="font-mono text-[11px] text-slate-600 dark:text-slate-400 break-all select-all">{{ resultadoValidacion.hash }}</span>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- KPI METRICS -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <Card class="border-slate-200 dark:border-slate-800">
          <CardContent class="p-4 flex items-center justify-between">
            <div>
              <span class="text-xs text-slate-500 font-medium">Documentos Emitidos</span>
              <div class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">{{ certificados.length }}</div>
              <span class="text-[11px] text-emerald-600 font-medium">2 válidos activos</span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 flex items-center justify-center">
              <FileCheck class="h-5 w-5" />
            </div>
          </CardContent>
        </Card>

        <Card class="border-slate-200 dark:border-slate-800">
          <CardContent class="p-4 flex items-center justify-between">
            <div>
              <span class="text-xs text-slate-500 font-medium">Títulos con QR</span>
              <div class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">{{ titulosQR.length }}</div>
              <span class="text-[11px] text-slate-400">Cohorte XVIII 2026</span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 flex items-center justify-center">
              <Award class="h-5 w-5" />
            </div>
          </CardContent>
        </Card>

        <Card class="border-slate-200 dark:border-slate-800">
          <CardContent class="p-4 flex items-center justify-between">
            <div>
              <span class="text-xs text-slate-500 font-medium">Documentos Revocados</span>
              <div class="text-2xl font-bold text-red-600 mt-1">1</div>
              <span class="text-[11px] text-slate-400">Por error de promedio</span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 flex items-center justify-center">
              <ShieldAlert class="h-5 w-5" />
            </div>
          </CardContent>
        </Card>

        <Card class="border-slate-200 dark:border-slate-800">
          <CardContent class="p-4 flex items-center justify-between">
            <div>
              <span class="text-xs text-slate-500 font-medium">Eventos de Seguridad</span>
              <div class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">{{ eventosAuditoria.length }}</div>
              <span class="text-[11px] text-emerald-600 font-medium">Auditoría DACE 100%</span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center">
              <History class="h-5 w-5" />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- TABS -->
      <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800">
        <button
          @click="tabActiva = 'documentos'"
          class="pb-3 text-xs sm:text-sm font-semibold border-b-2 transition-all flex items-center gap-2"
          :class="tabActiva === 'documentos' 
            ? 'border-red-600 text-red-600 dark:text-red-400' 
            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
        >
          <FileCheck class="h-4 w-4" />
          Certificaciones y Constancias Emitidas
        </button>

        <button
          @click="tabActiva = 'titulos'"
          class="pb-3 text-xs sm:text-sm font-semibold border-b-2 transition-all flex items-center gap-2"
          :class="tabActiva === 'titulos' 
            ? 'border-red-600 text-red-600 dark:text-red-400' 
            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
        >
          <Award class="h-4 w-4" />
          Títulos Universitarios Protocolizados
        </button>

        <button
          @click="tabActiva = 'eventos'"
          class="pb-3 text-xs sm:text-sm font-semibold border-b-2 transition-all flex items-center gap-2"
          :class="tabActiva === 'eventos' 
            ? 'border-red-600 text-red-600 dark:text-red-400' 
            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
        >
          <History class="h-4 w-4" />
          Bitácora de Seguridad y Auditoría
        </button>
      </div>

      <!-- TAB 1: CERTIFICACIONES EMITIDAS -->
      <div v-if="tabActiva === 'documentos'" class="space-y-4">
        <div
          v-for="doc in certificados"
          :key="doc.qvg_codigo"
          class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 p-5 shadow-sm space-y-3"
        >
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800/80 pb-3">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 flex items-center justify-center font-bold">
                <QrCode class="h-5 w-5" />
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <h4 class="text-base font-bold text-slate-900 dark:text-slate-100">{{ doc.tipo }}</h4>
                  <span class="font-mono text-xs text-indigo-600 font-bold">[{{ doc.qvg_unique_id }}]</span>
                </div>
                <span class="text-xs text-slate-500">{{ doc.estudiante }} • C.I. {{ doc.cedula }} • {{ doc.programa }}</span>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <Badge :variant="doc.qvg_status === 'VALIDO' ? 'successCustom' : 'destructiveCustom'">
                {{ doc.qvg_status }}
              </Badge>
              <Button size="xs" variant="outline" class="gap-1 text-xs" @click="inputHashBusqueda = doc.qvg_unique_id; verificarHash();">
                <ShieldCheck class="h-3.5 w-3.5" />
                Auditar
              </Button>
            </div>
          </div>

          <div class="grid gap-3 sm:grid-cols-2 text-xs">
            <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800">
              <span class="text-slate-400 block">Documento Emitido:</span>
              <span class="font-mono font-medium text-slate-800 dark:text-slate-200">{{ doc.qvg_pdf_name }}</span>
              <span class="text-[11px] text-slate-400 block mt-1">Emitido por: {{ doc.generador }}</span>
            </div>

            <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800">
              <span class="text-slate-400 block">Firma Hash SHA-256:</span>
              <span class="font-mono text-[11px] text-slate-700 dark:text-slate-300 break-all select-all">{{ doc.qvg_verification_hash }}</span>
              <span class="text-[11px] text-slate-400 block mt-1">Fecha Emisión: {{ doc.qvg_created_at }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 2: TÍTULOS CON QR -->
      <div v-if="tabActiva === 'titulos'" class="space-y-4">
        <div
          v-for="tit in titulosQR"
          :key="tit.qgt_codigo"
          class="rounded-2xl border border-amber-200 dark:border-amber-900/50 bg-white dark:bg-slate-950 p-5 shadow-sm space-y-3"
        >
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800/80 pb-3">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 flex items-center justify-center font-bold">
                <Award class="h-5 w-5" />
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <h4 class="text-base font-bold text-slate-900 dark:text-slate-100">{{ tit.titulo }}</h4>
                  <Badge class="bg-amber-500 text-white text-[10px]">{{ tit.mencion }}</Badge>
                </div>
                <span class="text-xs text-slate-500">Graduando: <strong>{{ tit.graduando }}</strong> (C.I. {{ tit.cedula }}) • {{ tit.tomo_folio }}</span>
              </div>
            </div>

            <Badge variant="successCustom" class="text-xs">
              {{ tit.qgt_status }}
            </Badge>
          </div>

          <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800 text-xs">
            <span class="text-slate-400 block">Firma Criptográfica del Título Oficial:</span>
            <span class="font-mono text-[11px] text-emerald-600 dark:text-emerald-400 break-all select-all">{{ tit.qgt_verification_hash }}</span>
          </div>
        </div>
      </div>

      <!-- TAB 3: BITÁCORA DE EVENTOS -->
      <div v-if="tabActiva === 'eventos'" class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-900/50 uppercase text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
              <tr>
                <th class="px-4 py-3 font-semibold">Fecha / Hora</th>
                <th class="px-4 py-3 font-semibold">Usuario</th>
                <th class="px-4 py-3 font-semibold">Formulario</th>
                <th class="px-4 py-3 font-semibold text-center">Tipo Evento</th>
                <th class="px-4 py-3 font-semibold">Dirección IP</th>
                <th class="px-4 py-3 font-semibold">Observación Detallada</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr v-for="eve in eventosAuditoria" :key="eve.eve_codigo" class="hover:bg-slate-50/60 dark:hover:bg-slate-900/30">
                <td class="px-4 py-3 font-mono text-slate-600 dark:text-slate-400">{{ eve.eve_fecha }}</td>
                <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">{{ eve.eve_usu_nombre }}</td>
                <td class="px-4 py-3 text-slate-500 font-mono">{{ eve.eve_formulario }}</td>
                <td class="px-4 py-3 text-center">
                  <Badge :variant="eve.eve_tipo_evento === 'QR_REVOKE' ? 'destructiveCustom' : 'outline'">
                    {{ eve.eve_tipo_evento }}
                  </Badge>
                </td>
                <td class="px-4 py-3 font-mono text-slate-500">{{ eve.eve_ip }}</td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ eve.eve_observacion }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- MODAL EMISIÓN DE DOCUMENTO CON QR -->
      <div v-if="modalEmision" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 animate-in fade-in">
        <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
              <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Emitir Documento con Código QR</h3>
              <p class="text-xs text-slate-500">Generación de documento oficial con código QR</p>
            </div>
            <button @click="modalEmision = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
          </div>

          <div class="space-y-3 text-xs">
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Tipo de Certificación Académica:
              </label>
              <select
                v-model="formEmision.tipo"
                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
              >
                <option value="Constancia de Estudios">Constancia de Estudios</option>
                <option value="Notas Certificadas">Notas Certificadas Oficiales</option>
                <option value="Carta de Culminación">Carta de Culminación de Carga Académica</option>
                <option value="Constancia de Buena Conducta">Constancia de Buena Conducta</option>
              </select>
            </div>

            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Cédula del Estudiante Titular:
              </label>
              <input
                v-model="formEmision.cedula"
                type="text"
                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
              />
            </div>

            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Programa Académico:
              </label>
              <input
                v-model="formEmision.programa"
                type="text"
                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
              />
            </div>

            <div class="p-3 rounded-lg bg-indigo-50/60 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-300 text-[11px]">
              Al emitir este documento se calculará automáticamente el hash SHA-256 y el token QR único para su verificación pública en el portal institucional.
            </div>
          </div>

          <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
            <Button size="sm" variant="outline" @click="modalEmision = false">Cancelar</Button>
            <Button size="sm" class="bg-red-600 hover:bg-red-700 text-white" @click="emitirDocumento">
              Generar Documento y QR
            </Button>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>
