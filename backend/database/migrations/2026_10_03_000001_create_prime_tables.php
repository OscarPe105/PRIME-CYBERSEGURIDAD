<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('users', function(Blueprint $t) {
   $t->string('role')->default('analista'); $t->text('mfa_secret')->nullable();
   $t->boolean('mfa_confirmed')->default(false); $t->bigInteger('mfa_last_step')->default(-1); $t->json('recovery_hashes')->nullable();
  });
  Schema::create('import_batches', function(Blueprint $t) {
   $t->id(); $t->foreignId('uploaded_by')->constrained('users'); $t->string('path'); $t->string('sha256',64); $t->unsignedInteger('count'); $t->timestamp('created_at');
  });
  Schema::create('findings', function(Blueprint $t) {
   $t->id(); $t->string('reference')->unique(); $t->string('cve')->nullable(); $t->string('asset');
   $t->string('zone'); $t->string('product'); $t->string('product_version');
   $t->foreignId('batch_id')->constrained('import_batches'); $t->json('input'); $t->string('status')->default('abierto'); $t->text('treatment')->nullable();
   $t->foreignId('proposed_by')->nullable()->constrained('users'); $t->foreignId('reviewed_by')->nullable()->constrained('users'); $t->timestamps();
  });
  Schema::create('triage_revisions', function(Blueprint $t) {
   $t->id(); $t->foreignId('finding_id')->constrained()->cascadeOnDelete();
   $t->string('policy_version'); $t->json('snapshot'); $t->json('calculation');
   $t->decimal('score',8,5); $t->string('priority'); $t->boolean('provisional'); $t->timestamp('created_at');
  });
  Schema::create('evidence_files', function(Blueprint $t) {
   $t->id(); $t->foreignId('finding_id')->constrained(); $t->foreignId('uploaded_by')->constrained('users');
   $t->string('path'); $t->string('name'); $t->string('sha256',64); $t->unsignedInteger('size'); $t->timestamp('created_at');
  });
  Schema::create('audit_events', function(Blueprint $t) {
   $t->id(); $t->foreignId('user_id')->nullable()->constrained(); $t->string('event');
   $t->json('details'); $t->timestamp('created_at');
  });
 }
 public function down(): void {
  Schema::dropIfExists('audit_events'); Schema::dropIfExists('evidence_files'); Schema::dropIfExists('triage_revisions'); Schema::dropIfExists('findings'); Schema::dropIfExists('import_batches');
  Schema::table('users',fn(Blueprint $t)=>$t->dropColumn(['role','mfa_secret','mfa_confirmed','mfa_last_step','recovery_hashes']));
 }
};
