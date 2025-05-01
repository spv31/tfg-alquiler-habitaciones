export const predefinedTokens = [
  { label: "Ciudad de firma", token: "ciudad_firma" },
  { label: "Día de firma", token: "dia_firma" },
  { label: "Mes de firma", token: "mes_firma" },
  { label: "Año de firma", token: "anio_firma" },

  { label: "Nombre arrendador 1", token: "arrendador1_nombre" },
  { label: "DNI/NIF arrendador 1", token: "arrendador1_dni" },
  { label: "Nombre arrendador 2", token: "arrendador2_nombre" },
  { label: "DNI/NIF arrendador 2", token: "arrendador2_dni" },
  { label: "Estado civil arrendador", token: "arrendador_estado_civil" },
  { label: "Régimen arrendador", token: "arrendador_regimen" },
  { label: "Municipio arrendador", token: "arrendador_municipio" },
  { label: "Calle arrendador", token: "arrendador_calle" },

  { label: "Nombre arrendatario 1", token: "arrendatario1_nombre" },
  { label: "DNI/NIF arrendatario 1", token: "arrendatario1_dni" },
  { label: "Nombre arrendatario 2", token: "arrendatario2_nombre" },
  { label: "DNI/NIF arrendatario 2", token: "arrendatario2_dni" },
  { label: "Estado civil arrendatario", token: "arrendatario_estado_civil" },
  { label: "Régimen arrendatario", token: "arrendatario_regimen" },
  { label: "Municipio arrendatario", token: "arrendatario_municipio" },
  { label: "Calle arrendatario", token: "arrendatario_calle" },

  { label: "Nombre avalista 1", token: "avalista1_nombre" },
  { label: "DNI/NIF avalista 1", token: "avalista1_dni" },
  { label: "Nombre avalista 2", token: "avalista2_nombre" },
  { label: "DNI/NIF avalista 2", token: "avalista2_dni" },
  { label: "Estado civil avalista", token: "avalista_estado_civil" },
  { label: "Régimen avalista", token: "avalista_regimen" },
  { label: "Municipio avalista", token: "avalista_municipio" },
  { label: "Calle avalista", token: "avalista_calle" },

  { label: "Dirección completa finca", token: "finca_direccion" },
  { label: "Ciudad de la finca", token: "finca_ciudad" },
  { label: "Superficie construida (m²)", token: "finca_m2_construido" },
  { label: "Superficie útil (m²)", token: "finca_m2_util" },
  { label: "Número total de habitaciones", token: "vivienda_num_habitaciones" },
  { label: "Número total de baños", token: "vivienda_num_banos" },

  { label: "Referencia catastral", token: "finca_ref_catastral" },

  { label: "Registro Nº", token: "finca_registro_num" },
  {
    label: "Registro de la Propiedad (localidad)",
    token: "finca_registro_localidad",
  },
  { label: "Tomo", token: "finca_registro_tomo" },
  { label: "Libro", token: "finca_registro_libro" },
  { label: "Folio", token: "finca_registro_folio" },
  { label: "Finca", token: "finca_registro_finca" },

  { label: "Notario", token: "notario_nombre" },
  { label: "Municipio notario", token: "notario_municipio" },
  { label: "Fecha escritura", token: "fecha_escritura" },

  { label: "Fecha inicio arrendamiento", token: "fecha_inicio" },
  { label: "Duración (meses)", token: "duracion_meses" },
  { label: "Fecha fin arrendamiento", token: "fecha_fin" },
  { label: "Día límite de pago mensual", token: "renta_dia_pago" },

  { label: "Renta anual pactada (€)", token: "renta_anual" },
  { label: "Renta mensual (€)", token: "renta_mensual" },
  { label: "Fianza (€)", token: "fianza_importe" },

  { label: "Titular cuenta", token: "banco_titular" },
  { label: "IBAN completo", token: "banco_iban" },
  { label: "Entidad bancaria", token: "banco_entidad" },
  { label: "Oficina bancaria", token: "banco_oficina" },
  { label: "DC", token: "banco_dc" },
  { label: "Número de cuenta", token: "banco_num_cuenta" },

  {
    label: "Dirección postal arrendatario",
    token: "notif_arrendatario_direccion",
  },
  { label: "Email arrendatario", token: "notif_arrendatario_email" },
  { label: "Teléfono arrendatario", token: "notif_arrendatario_telefono" },
  { label: "Dirección postal arrendador", token: "notif_arrendador_direccion" },
  { label: "Email arrendador", token: "notif_arrendador_email" },
  { label: "Teléfono arrendador", token: "notif_arrendador_telefono" },
  { label: "Dirección postal avalista", token: "notif_avalista_direccion" },
  { label: "Email avalista", token: "notif_avalista_email" },
  { label: "Teléfono avalista", token: "notif_avalista_telefono" },

  { label: "Número de habitación", token: "habitacion_numero" },
  { label: "Descripción de la habitación", token: "habitacion_descripcion" },
  { label: "Superficie habitación (m²)", token: "habitacion_m2" },

  { label: "Gastos incluidos (sí/no)", token: "gastos_incluidos" },
  { label: "Pagar gastos quién", token: "gastos_paga" }, // "arrendador"|"arrendatario"|"compartido"
  { label: "Proporción gastos (%)", token: "gastos_porcentaje" },

  { label: "Garantías adicionales", token: "garantias_extra" },
  {
    label: "Responsabilidad / prohibiciones",
    token: "responsabilidad_prohibiciones",
  },

  { label: "Tipo de MASC", token: "masc_tipo" },
  { label: "Nombre profesional MASC", token: "masc_profesional_nombre" },
  { label: "Email profesional MASC", token: "masc_profesional_email" },
  { label: "Teléfono profesional MASC", token: "masc_profesional_telefono" },

  { label: "Otras cláusulas", token: "otras_clausulas" },
];
