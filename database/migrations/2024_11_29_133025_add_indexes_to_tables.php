<?php

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
        Schema::table('attendances', function (Blueprint $table) {
            $table->index('urc_id', 'idx_attendances_urc_id');
            $table->index('ticket_id', 'idx_attendances_ticket_id');
            $table->index('attendance_status_id', 'idx_attendances_attendance_status_id');
            $table->index('patient_id', 'idx_attendances_patient_id');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->index('ticket_sequence_per_urgency_regulation_center', 'idx_tickets_ticket_sequence_per_urgency_regulation_center');
            $table->index('city_id', 'idx_tickets_city_id');
            $table->index('requester_id', 'idx_tickets_requester_id');
            $table->index('created_by', 'idx_tickets_created_by');
        });

        Schema::table('scene_recordings', function (Blueprint $table) {
            $table->index('attendance_id', 'idx_scene_recordings_attendance_id');
            $table->index('created_at', 'idx_scene_recordings_created_at');
        });

        Schema::table('medical_regulations', function (Blueprint $table) {
            $table->index('attendance_id', 'idx_medical_regulations_attendance_id');
            $table->index('created_at', 'idx_medical_regulations_created_at');
            $table->index('created_by', 'idx_medical_regulations_created_by');
        });

        Schema::table('scene_recording_counterreferrals', function (Blueprint $table) {
            $table->index('scene_recording_id', 'idx_scene_recording_counterreferrals_scene_recording_id');
            $table->index('created_at', 'idx_scene_recording_counterreferrals_created_at');
        });

        Schema::table('user_attendances', function (Blueprint $table) {
            $table->index('attendance_id', 'idx_user_attendances_attendance_id');
            $table->index('created_at', 'idx_user_attendances_created_at');
            $table->index('user_id', 'idx_user_attendances_user_id');
        });

        Schema::table('vehicle_status_histories', function (Blueprint $table) {
            $table->index('attendance_id', 'idx_vehicle_status_histories_attendance_id');
            $table->index('vehicle_id', 'idx_vehicle_status_histories_vehicle_id');
            $table->index('created_at', 'idx_vehicle_status_histories_created_at');
        });

        Schema::table('requesters', function (Blueprint $table) {
            $table->index('primary_phone', 'idx_requesters_primary_phone');
            $table->index('secondary_phone', 'idx_requesters_secondary_phone');
            $table->index('requester_type_id', 'idx_requesters_requester_type_id');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->index('name', 'idx_patients_name');
            $table->index('age', 'idx_patients_age');
            $table->index('time_unit_id', 'idx_patients_time_unit_id');
        });

        Schema::table('form_diagnostic_hypotheses', function (Blueprint $table) {
            $table->index('attendance_id', 'idx_form_diagnostic_hypotheses_attendance_id');
            $table->index('nature_type_id', 'idx_form_diagnostic_hypotheses_nature_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropIndex('idx_attendances_urc_id');
                $table->dropIndex('idx_attendances_ticket_id');
                $table->dropIndex('idx_attendances_attendance_status_id');
                $table->dropIndex('idx_attendances_patient_id');
            });

            Schema::table('tickets', function (Blueprint $table) {
                $table->dropIndex('idx_tickets_ticket_sequence_per_urgency_regulation_center');
                $table->dropIndex('idx_tickets_city_id');
                $table->dropIndex('idx_tickets_requester_id');
                $table->dropIndex('idx_tickets_created_by');
            });

            Schema::table('scene_recordings', function (Blueprint $table) {
                $table->dropIndex('idx_scene_recordings_attendance_id');
                $table->dropIndex('idx_scene_recordings_created_at');
            });

            Schema::table('medical_regulations', function (Blueprint $table) {
                $table->dropIndex('idx_medical_regulations_attendance_id');
                $table->dropIndex('idx_medical_regulations_created_at');
                $table->dropIndex('idx_medical_regulations_created_by');
            });

            Schema::table('scene_recording_counterreferrals', function (Blueprint $table) {
                $table->dropIndex('idx_scene_recording_counterreferrals_scene_recording_id');
                $table->dropIndex('idx_scene_recording_counterreferrals_created_at');
            });

            Schema::table('user_attendances', function (Blueprint $table) {
                $table->dropIndex('idx_user_attendances_attendance_id');
                $table->dropIndex('idx_user_attendances_created_at');
                $table->dropIndex('idx_user_attendances_user_id');
            });

            Schema::table('vehicle_status_histories', function (Blueprint $table) {
                $table->dropIndex('idx_vehicle_status_histories_attendance_id');
                $table->dropIndex('idx_vehicle_status_histories_vehicle_id');
                $table->dropIndex('idx_vehicle_status_histories_created_at');
            });

            Schema::table('requesters', function (Blueprint $table) {
                $table->dropIndex('idx_requesters_primary_phone');
                $table->dropIndex('idx_requesters_secondary_phone');
                $table->dropIndex('idx_requesters_requester_type_id');
            });

            Schema::table('patients', function (Blueprint $table) {
                $table->dropIndex('idx_patients_name');
                $table->dropIndex('idx_patients_age');
                $table->dropIndex('idx_patients_time_unit_id');
            });

            Schema::table('form_diagnostic_hypotheses', function (Blueprint $table) {
                $table->dropIndex('idx_form_diagnostic_hypotheses_attendance_id');
                $table->dropIndex('idx_form_diagnostic_hypotheses_nature_type_id');
            });
        });
    }
};
