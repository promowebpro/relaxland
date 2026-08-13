<?php

use App\Domain\Leads\LeadStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->nullable();
            $table->string('phone', 32);
            $table->string('email', 254)->nullable();
            $table->text('message')->nullable();
            $table->string('source', 50);
            $table->string('form_type', 50);
            $table->string('page_url', 2048)->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('status', 32)->default(LeadStatus::New->value)->index();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('manager_comment')->nullable();
            $table->timestamp('consent_given_at')->nullable();
            $table->foreignId('privacy_document_id')->nullable()->constrained('legal_documents')->nullOnDelete();
            $table->string('privacy_document_version')->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index('assigned_to');
            $table->index(['source', 'form_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
