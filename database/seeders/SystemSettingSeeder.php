<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key'         => 'allow_public_booking',
                'value'       => '1',
                'type'        => 'boolean',
                'label'       => 'Agendamento Público Ativo',
                'description' => 'Permite que pacientes agendem consultas pelo portal público.',
            ],
            [
                'key'         => 'allow_patient_registration',
                'value'       => '1',
                'type'        => 'boolean',
                'label'       => 'Permitir Novos Cadastros',
                'description' => 'Permite o cadastro de novos pacientes pelo portal público.',
            ],
            [
                'key'         => 'appointment_fee',
                'value'       => '150.00',
                'type'        => 'decimal',
                'label'       => 'Taxa de Consulta (R$)',
                'description' => 'Valor padrão cobrado por consulta particular.',
            ],
            [
                'key'         => 'max_daily_appointments',
                'value'       => '20',
                'type'        => 'integer',
                'label'       => 'Máx. Consultas por Dia',
                'description' => 'Limite diário de agendamentos aceitos pelo sistema.',
            ],
            [
                'key'         => 'clinic_name',
                'value'       => 'Clínica DR.João Mendes',
                'type'        => 'string',
                'label'       => 'Nome da Clínica',
                'description' => 'Nome exibido em documentos e notificações.',
            ],
            [
                'key'         => 'clinic_cnpj',
                'value'       => '',
                'type'        => 'string',
                'label'       => 'CNPJ',
                'description' => 'CNPJ da clínica para emissão de documentos fiscais.',
            ],
            [
                'key'         => 'clinic_phone',
                'value'       => '',
                'type'        => 'string',
                'label'       => 'Telefone',
                'description' => 'Telefone principal da clínica.',
            ],
            [
                'key'         => 'clinic_address',
                'value'       => '',
                'type'        => 'string',
                'label'       => 'Endereço',
                'description' => 'Endereço completo da clínica.',
            ],
            [
                'key'         => 'email_notifications',
                'value'       => '1',
                'type'        => 'boolean',
                'label'       => 'Notificações por Email',
                'description' => 'Envia emails automáticos para confirmação de consultas.',
            ],
            [
                'key'         => 'default_appointment_duration',
                'value'       => '30',
                'type'        => 'integer',
                'label'       => 'Duração Padrão (min)',
                'description' => 'Duração padrão de consultas em minutos ao criar agendamento.',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
