<?php

use App\Models\Property;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('property_details', function (Blueprint $table) {
            $table->id();
            // Relación
            $table->foreignIdFor(Property::class)->constrained()->cascadeOnDelete();
            
            // Datos opcionales propiedad
            $table->decimal('purchase_price', 10, 2)->nullable();
            // Boolean (financiada / no financiada)
            $table->boolean('is_financed')->nullable();
            // Coste hipoteca
            $table->decimal('mortgage_cost', 10, 2)->nullable();
            // Impuestos compra
            $table->decimal('purchase_taxes', 10, 2)->nullable();
            // Coste reforma
            $table->decimal('renovation_cost', 10, 2)->nullable();
            // Coste amueblar
            $table->decimal('furniture_cost', 10, 2)->nullable();
            // Fecha compra
            $table->date('purchase_date')->nullable();
            // Estimación actual valor inmueble
            $table->decimal('estimated_value', 10, 2)->nullable();
            // Coste anual seguros
            $table->decimal('annual_insurance_cost', 10, 2)->nullable();
            // Coste anual IBI (o otro impuesto de otro pais)
            $table->decimal('annual_property_tax', 10, 2)->nullable();
            // Gastos anuales de comunidad
            $table->decimal('annual_community_fees', 10, 2)->nullable();
            // Impuesto anual basuras
            $table->decimal('annual_waste_tax', 10, 2)->nullable();
            // IRPF Inquilino particular (o impuesto similar)
             $table->decimal('income_tax_percentage', 5, 2)->nullable();
            // Estimacion % del valor para reparaciones
            $table->decimal('annual_repair_percentage', 5, 2)->nullable();
            // Precio alquiler (por completo)
            $table->decimal('rental_price', 10, 2)->nullable();
            // Tamaño m² -> calcular rentabilidad por m²
            $table->decimal('property_size', 10, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_details');
    }
};
