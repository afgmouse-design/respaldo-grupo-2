-- =========================================================================
-- SISTEMA DE GESTIÓN ACADÉMICA (SOGAC) - SCRIPT DDL POSTGRESQL
-- =========================================================================

-- -------------------------------------------------------------------------
-- 1. MÓDULO GEOGRÁFICO
-- -------------------------------------------------------------------------

CREATE TABLE pais (
    id_pais SERIAL PRIMARY KEY,
    nombre_pai VARCHAR(255) NOT NULL,
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE estado (
    id_esd SERIAL PRIMARY KEY,
    nombre_esd VARCHAR(255) NOT NULL,
    pai_id INTEGER NOT NULL REFERENCES pais(id_pais),
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE municipio (
    id_mun SERIAL PRIMARY KEY,
    nombre_mun VARCHAR(255) NOT NULL,
    esd_id INTEGER NOT NULL REFERENCES estado(id_esd),
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE parroquia (
    id_par SERIAL PRIMARY KEY,
    nombre_par VARCHAR(255) NOT NULL,
    mun_id INTEGER NOT NULL REFERENCES municipio(id_mun),
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE localidad (
    id_loc SERIAL PRIMARY KEY,
    nombre_loc VARCHAR(255) NOT NULL,
    par_id INTEGER NOT NULL REFERENCES parroquia(id_par),
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

-- -------------------------------------------------------------------------
-- 2. MÓDULO INSTITUCIONAL
-- -------------------------------------------------------------------------

CREATE TABLE universidad (
    id_uni SERIAL PRIMARY KEY,
    codigo_ministerio_uni VARCHAR(30),
    nombre_uni VARCHAR(255) NOT NULL,
    siglas_uni VARCHAR(20),
    rif_uni VARCHAR(20),
    gaceta_uni VARCHAR(255),
    fecha_gaceta_uni DATE,
    decreto_uni VARCHAR(255),
    fecha_decreto_uni DATE,
    telefono_uni VARCHAR(50),
    correo_uni VARCHAR(255),
    sitio_web_uni VARCHAR(255),
    logo_uni TEXT,
    fecha_inicio_uni DATE,
    fecha_fin_uni DATE,
    dpi_id INTEGER,
    cdn_id INTEGER,
    loc_id INTEGER REFERENCES localidad(id_loc),
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE sede (
    id_sed SERIAL PRIMARY KEY,
    nombre_sed VARCHAR(255) NOT NULL,
    siglas_sed VARCHAR(20),
    codigo_ministerio_sed VARCHAR(30),
    direccion_sed TEXT,
    telefono_sed VARCHAR(50),
    correo_sed VARCHAR(255),
    gaceta_sed VARCHAR(255),
    fecha_gaceta_sed DATE,
    resolucion_sed VARCHAR(255),
    fecha_resolucion_sed DATE,
    fecha_inicio_sed DATE,
    fecha_fin_sed DATE,
    uni_id INTEGER NOT NULL REFERENCES universidad(id_uni),
    loc_id INTEGER NOT NULL REFERENCES localidad(id_loc),
    cdn_id INTEGER,
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

-- -------------------------------------------------------------------------
-- 3. MÓDULO ESTRUCTURA ACADÉMICA
-- -------------------------------------------------------------------------

CREATE TABLE tipo_programa (
    id_tpg SERIAL PRIMARY KEY,
    codigo_tpg VARCHAR(20) NOT NULL,
    nombre_tpg VARCHAR(100) NOT NULL,
    descripcion_tpg TEXT,
    conducente_grado_tpg BOOLEAN DEFAULT TRUE,
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE modalidad_estudio (
    id_moe SERIAL PRIMARY KEY,
    codigo_moe VARCHAR(20) NOT NULL,
    nombre_moe VARCHAR(100) NOT NULL,
    descripcion_moe TEXT,
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE regimen_estudio (
    id_ree SERIAL PRIMARY KEY,
    codigo_ree VARCHAR(20) NOT NULL,
    nombre_ree VARCHAR(100) NOT NULL,
    descripcion_ree TEXT,
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE eje_curricular (
    id_ecu SERIAL PRIMARY KEY,
    nombre_ecu VARCHAR(100) NOT NULL,
    codigo_ecu VARCHAR(20),
    descripcion_ecu TEXT,
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE tipo_unidad_curricular (
    id_tuc SERIAL PRIMARY KEY,
    nombre_tuc VARCHAR(150) NOT NULL,
    codigo_tuc VARCHAR(30),
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE unidad_curricular (
    id_unc SERIAL PRIMARY KEY,
    codigo_unc VARCHAR(20) NOT NULL,
    codigo_anterior_unc VARCHAR(20),
    nombre_unc VARCHAR(255) NOT NULL,
    tuc_id INTEGER NOT NULL REFERENCES tipo_unidad_curricular(id_tuc),
    dep_id INTEGER,
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE tipo_periodo (
    id_tpd SERIAL PRIMARY KEY,
    nombre_tpd VARCHAR(100) NOT NULL,
    codigo_tpd VARCHAR(30),
    duracion_semanas_tpd INTEGER,
    descripcion_tpd TEXT,
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE ano_academico (
    id_ana SERIAL PRIMARY KEY,
    nombre_ana VARCHAR(255) NOT NULL,
    fecha_inicio_ana DATE,
    fecha_fin_ana DATE,
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE lapso_academico (
    id_laa SERIAL PRIMARY KEY,
    codigo_laa VARCHAR(30) NOT NULL,
    nombre_laa VARCHAR(255) NOT NULL,
    fecha_inicio_laa DATE NOT NULL,
    fecha_fin_laa DATE NOT NULL,
    fase_operativa_laa VARCHAR(50),
    ana_id INTEGER NOT NULL REFERENCES ano_academico(id_ana),
    tpd_id INTEGER NOT NULL REFERENCES tipo_periodo(id_tpd),
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE corte_lapso (
    id_col SERIAL PRIMARY KEY,
    numero_corte_col INTEGER,
    porcentaje_valor_col NUMERIC,
    fecha_apertura_notas_col TIMESTAMPTZ,
    fecha_cierre_notas_col TIMESTAMPTZ,
    laa_id INTEGER NOT NULL REFERENCES lapso_academico(id_laa),
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE programa (
    id_prg SERIAL PRIMARY KEY,
    codigo_prg VARCHAR(20),
    codigo_ministerio_prg VARCHAR(30),
    nombre_prg VARCHAR(255) NOT NULL,
    siglas_prg VARCHAR(20),
    tipo_prg VARCHAR(50),
    gaceta_prg VARCHAR(100),
    fecha_gaceta_prg DATE,
    resolucion_prg VARCHAR(100),
    fecha_resolucion_prg DATE,
    tiene_salida_intermedia_prg BOOLEAN DEFAULT FALSE,
    titulo_prg VARCHAR(255),
    tpg_id INTEGER REFERENCES tipo_programa(id_tpg),
    uni_id INTEGER REFERENCES universidad(id_uni),
    moe_id INTEGER REFERENCES modalidad_estudio(id_moe),
    ree_id INTEGER REFERENCES regimen_estudio(id_ree),
    exm_id INTEGER,
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE malla_curricular (
    id_mac SERIAL PRIMARY KEY,
    nombre_mac VARCHAR(255) NOT NULL,
    codigo_gaceta_mac VARCHAR(100),
    fecha_aprobacion_mac DATE,
    creditos_minimos_tsu_mac INTEGER,
    creditos_minimos_ing_mac INTEGER,
    creditos_minimos_prosecucion_mac INTEGER,
    horas_minimas_tsu_mac INTEGER,
    horas_minimas_ing_mac INTEGER,
    observacion_mac TEXT,
    prg_id INTEGER NOT NULL REFERENCES programa(id_prg),
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE trayecto (
    id_tra SERIAL PRIMARY KEY,
    codigo_tra VARCHAR(20),
    nombre_tra VARCHAR(100) NOT NULL,
    numero_orden_tra INTEGER NOT NULL,
    tpd_id INTEGER NOT NULL REFERENCES tipo_periodo(id_tpd),
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE malla_curricular_detalle (
    id_mcd SERIAL PRIMARY KEY,
    unidades_credito_mcd INTEGER,
    htea_mcd INTEGER,
    htei_mcd INTEGER,
    thte_mcd INTEGER,
    duracion_semanas_mcd INTEGER,
    nota_minima_aprobatoria_mcd NUMERIC(4,2),
    mac_id INTEGER NOT NULL REFERENCES malla_curricular(id_mac),
    unc_id INTEGER NOT NULL REFERENCES unidad_curricular(id_unc),
    tra_id INTEGER NOT NULL REFERENCES trayecto(id_tra),
    ecu_id INTEGER REFERENCES eje_curricular(id_ecu),
    tuc_id INTEGER REFERENCES tipo_unidad_curricular(id_tuc),
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE cohorte (
    id_coh SERIAL PRIMARY KEY,
    nombre_coh VARCHAR(255),
    fecha_inicio_coh DATE,
    laa_id INTEGER NOT NULL REFERENCES lapso_academico(id_laa),
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE cohorte_sede (
    id_cos SERIAL PRIMARY KEY,
    nombre_cos VARCHAR(255),
    coh_id INTEGER NOT NULL REFERENCES cohorte(id_coh),
    sep_id INTEGER NOT NULL,
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

-- -------------------------------------------------------------------------
-- 4. MÓDULO OFERTA Y HORARIOS
-- -------------------------------------------------------------------------

CREATE TABLE tipo_seccion (
    id_tis SERIAL PRIMARY KEY,
    codigo_tis VARCHAR(20) NOT NULL,
    nombre_tis VARCHAR(100) NOT NULL,
    descripcion_tis TEXT,
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE turno (
    id_tur SERIAL PRIMARY KEY,
    codigo_tur VARCHAR(20) NOT NULL,
    nombre_tur VARCHAR(100) NOT NULL,
    hora_inicio_tur TIME,
    hora_fin_tur TIME,
    descripcion_tur TEXT,
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE tipo_aula (
    id_tia SERIAL PRIMARY KEY,
    nombre_tia VARCHAR(150) NOT NULL,
    codigo_tia VARCHAR(30),
    descripcion_tia TEXT,
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE aula (
    id_aul SERIAL PRIMARY KEY,
    codigo_aula_aul VARCHAR(255) NOT NULL,
    capacidad_maxima_aul INTEGER,
    tipo_espacio_aul VARCHAR(255),
    sed_id INTEGER NOT NULL REFERENCES sede(id_sed),
    tia_id INTEGER NOT NULL REFERENCES tipo_aula(id_tia),
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE seccion (
    id_sec SERIAL PRIMARY KEY,
    nivel_sec INTEGER,
    nombre_sec VARCHAR(255) NOT NULL,
    turno_sec VARCHAR(255),
    cupo_maximo_sec INTEGER,
    laa_id INTEGER NOT NULL REFERENCES lapso_academico(id_laa),
    prg_id INTEGER NOT NULL REFERENCES programa(id_prg),
    sed_id INTEGER NOT NULL REFERENCES sede(id_sed),
    tis_id INTEGER REFERENCES tipo_seccion(id_tis),
    tur_id INTEGER REFERENCES turno(id_tur),
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE carga_academica (
    id_caa SERIAL PRIMARY KEY,
    sec_id INTEGER NOT NULL REFERENCES seccion(id_sec),
    unc_id INTEGER NOT NULL REFERENCES unidad_curricular(id_unc),
    docente_per_id INTEGER, -- Permite NULL para admitir docentes en proceso o pendientes
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

CREATE TABLE horario (
    id_hor SERIAL PRIMARY KEY,
    dia_semana_hor VARCHAR(255) NOT NULL,
    hora_inicio_hor TIME NOT NULL,
    hora_fin_hor TIME NOT NULL,
    caa_id INTEGER NOT NULL REFERENCES carga_academica(id_caa),
    aul_id INTEGER NOT NULL REFERENCES aula(id_aul),
    status_id INTEGER DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMPTZ,
    created_id INTEGER,
    updated_id INTEGER
);

