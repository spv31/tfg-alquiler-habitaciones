<template>
  <div class="min-h-screen max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div v-if="alertMessage" class="mb-4 mx-auto">
      <Alert
        :message="alertMessage"
        :type="alertType"
        @close="alertMessage = ''"
      />
    </div>

    <div class="text-center md:text-right">
      <NuxtLink
        :to="$localePath('contract-templates-create')"
        class="button-primary inline-block"
      >
        Añadir una plantilla
      </NuxtLink>
    </div>

    <div
      class="contract mt-6 bg-white border-2 border-gray-200 rounded-xl p-6 px-8 shadow-lg hover:shadow-xl transition-all duration-300 ease-in-out transform hover:-translate-y-1 cursor-pointer mx-auto max-w-[1200px]"
    >
      <h1>CONTRATO DE ARRENDAMIENTO DE VIVIENDA</h1>

      <p style="text-align: right">
        En <strong>%lugar_firma%</strong>, a <strong>%dia_firma%</strong> de
        <strong>%mes_firma%</strong> de <strong>%anio_firma%</strong>
      </p>

      <h2>REUNIDOS</h2>
      <p>
        De una parte, D.&nbsp;<strong>%propietario_nombre%</strong>, mayor de
        edad, con DNI
        <strong>%propietario_dni%</strong>, y domicilio a efectos de
        notificaciones en <strong>%propietario_domicilio%</strong>.
      </p>
      <p>
        De otra parte, D.&nbsp;<strong>%inquilino_nombre%</strong>, mayor de
        edad, con DNI
        <strong>%inquilino_dni%</strong>, y domicilio a efectos de
        notificaciones en <strong>%inquilino_domicilio%</strong>.
      </p>

      <h2>INTERVIENEN</h2>
      <p>
        D.&nbsp;%propietario_nombre% interviene en su propio nombre y derecho
        como
        <strong>parte arrendadora</strong>.
      </p>
      <p>
        D.&nbsp;%inquilino_nombre% interviene en su propio nombre y derecho como
        <strong>parte arrendataria</strong>, actuando esta última bajo
        responsabilidad solidaria.
      </p>
      <p>
        Reconociéndose ambas partes capacidad legal suficiente, otorgan el
        presente contrato.
      </p>

      <h2>EXPONEN</h2>
      <p>
        <strong>Primero.</strong> Que la parte arrendadora es propietaria de la
        vivienda sita en <strong>%inmueble_direccion%</strong>, cuyas
        características son conocidas por ambas partes. Se acompaña Certificado
        de Eficiencia Energética (Anexo I).
      </p>

      <p>
        <strong>Segundo.</strong> Que la parte arrendataria manifiesta que ni
        ella ni su unidad familiar se encuentran en situación de vulnerabilidad
        económica y/o social.
      </p>

      <p>
        <strong>Tercero.</strong> Que ambas partes desean formalizar el
        arrendamiento del citado inmueble y, a tal fin, convienen las
        siguientes <strong>CLÁUSULAS</strong>:
      </p>

      <div class="page-break"></div>

      <h3>1.&nbsp;OBJETO Y DESTINO</h3>
      <p>
        La arrendadora arrienda a la arrendataria la vivienda descrita, que ésta
        acepta como <em>cuerpo cierto</em>. Se destinará a vivienda habitual
        propia y de su familia.
      </p>

      <h3>2.&nbsp;DURACIÓN, PRÓRROGAS Y FINALIZACIÓN</h3>
      <p>
        Plazo inicial: <strong>%plazo_inicial%</strong>.<br />
        Fecha inicio: <strong>%fecha_inicio%</strong> – Fin:
        <strong>%fecha_fin%</strong>.<br />
        Fin prórroga (si procede): <strong>%fecha_fin_prorroga%</strong>.
      </p>
      <p>
        Las partes se someten a los arts.&nbsp;9 y&nbsp;10 LAU y renuncian a las
        prórrogas extraordinarias de los arts.&nbsp;10.2‑10.3.
      </p>
      <p>
        <u>Necesidad del arrendador (art.&nbsp;9.3&nbsp;LAU)</u>: el arrendador
        podrá recuperar la vivienda con preaviso de dos meses si la necesita
        para sí o familiares en primer grado…
      </p>
      <p>
        <u>Desistimiento (art.&nbsp;11&nbsp;LAU)</u>: %clausula_desistimiento%
      </p>
      <p><u>Penalizaciones</u>: %clausula_penalizaciones%</p>

      <h3>3.&nbsp;RENTA Y ACTUALIZACIÓN</h3>
      <p>
        Renta mensual: <strong>%precio_mensual% €</strong>.<br />
        Forma de pago: transferencia antes del día 5 a la cuenta
        <strong>%cuenta_bancaria%</strong>.<br />
        Fianza: <strong>%fianza% €</strong>. Garantía adicional:
        <strong>%garantia_adicional%</strong>.
      </p>
      <p>
        Actualización anual según IPC publicado por el INE (doce meses
        previos) …
      </p>

      <h3>4.&nbsp;GASTOS GENERALES</h3>
      <p>%gastos_generales_clausula%</p>

      <h3>5.&nbsp;SUMINISTROS</h3>
      <p>%clausula_suministros%</p>

      <h3>6.&nbsp;OBRAS Y REPARACIONES</h3>
      <p>Las partes se someten a los arts.&nbsp;21‑24 LAU.</p>

      <div class="page-break"></div>

      <h3>7.&nbsp;NORMAS DE CONVIVENCIA</h3>
      <p>
        La arrendataria respetará las normas de la comunidad. Se prohíben
        animales salvo consentimiento escrito.
      </p>

      <h3>8.&nbsp;CESIÓN Y SUBARRIENDO</h3>
      <p>
        Prohibidos sin consentimiento expreso del arrendador. Su incumplimiento
        supone resolución del contrato.
      </p>

      <h3>9.&nbsp;DERECHO DE ADQUISICIÓN PREFERENTE</h3>
      <p>La arrendataria renuncia a este derecho según art.&nbsp;25.8 LAU.</p>

      <h3>10.&nbsp;FIANZA Y GARANTÍAS</h3>
      <p>
        Fianza: <strong>%fianza% €</strong> (art.&nbsp;36.1 LAU).<br />
        Garantía adicional:
        <strong>%garantia_adicional%</strong>
        (<em>%garantia_meses%</em> mensualidades).<br />
        No podrán aplicarse a rentas impagadas salvo acuerdo expreso.
      </p>

      <h3>11.&nbsp;DOMICILIO A EFECTOS DE NOTIFICACIONES</h3>
      <p>
        Arrendador: <strong>%correo_propietario%</strong><br />
        Arrendatario: <strong>%correo_inquilino%</strong><br />
        Cualquier cambio debe notificarse por escrito.
      </p>

      <h3>12.&nbsp;RÉGIMEN APLICABLE</h3>
      <p>Se aplica la LAU (art.&nbsp;4) y supletoriamente el Código Civil.</p>

      <h3>13.&nbsp;SOLUCIÓN DE CONFLICTOS</h3>
      <p>%clausula_solucion_conflictos%</p>

      <h3>14.&nbsp;PROTECCIÓN DE DATOS</h3>
      <p>Ambas partes declaran conocer y cumplir RGPD y LOPDGDD …</p>

      <br /><br />

      <table style="width: 100%; margin-top: 40px">
        <tr>
          <td style="width: 50%; text-align: center">
            ___________________________<br />
            <strong>PARTE ARRENDADORA</strong>
          </td>
          <td style="width: 50%; text-align: center">
            ___________________________<br />
            <strong>PARTE ARRENDATARIA</strong>
          </td>
        </tr>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
</script>

<style scoped>
.contract {
  font-family: "Times New Roman", serif;
  font-size: 11pt;
  line-height: 1.4;
}
h1,
h2 {
  text-align: center;
  margin: 0 0 12px;
}
.page-break {
  page-break-before: always;
}
</style>