<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // ─── Hospital ─────────────────────────────────────────────────
            ['label' => 'Dashboard',          'route' => 'dashboard',           'icon' => 'heroicon-o-squares-2x2',         'group' => 'Hospital',            'min_level' => 4, 'is_visible' => true,  'order' => 1],
            ['label' => 'Agendamentos',        'route' => 'appointments.index',  'icon' => 'heroicon-o-calendar-days',       'group' => 'Hospital',            'min_level' => 3, 'is_visible' => true,  'order' => 2],
            ['label' => 'Médicos',             'route' => 'doctors.index',       'icon' => 'heroicon-o-user-circle',         'group' => 'Hospital',            'min_level' => 4, 'is_visible' => true,  'order' => 3],
            ['label' => 'Pacientes',           'route' => 'patients.index',      'icon' => 'heroicon-o-users',               'group' => 'Hospital',            'min_level' => 3, 'is_visible' => true,  'order' => 4],
            ['label' => 'Leitos e Salas',      'route' => 'rooms.index',         'icon' => 'heroicon-o-building-office',     'group' => 'Hospital',            'min_level' => 3, 'is_visible' => true,  'order' => 5],
            ['label' => 'Pagamentos',          'route' => 'payments.index',      'icon' => 'heroicon-o-banknotes',           'group' => 'Hospital',            'min_level' => 4, 'is_visible' => true,  'order' => 6],
            ['label' => 'Despesas',            'route' => 'expenses.index',      'icon' => 'heroicon-o-chart-bar',           'group' => 'Hospital',            'min_level' => 4, 'is_visible' => true,  'order' => 7],
            ['label' => 'Departamentos',       'route' => 'departments.index',   'icon' => 'heroicon-o-building-office-2',   'group' => 'Hospital',            'min_level' => 1, 'is_visible' => true,  'order' => 8],
            ['label' => 'Convênios',           'route' => 'insurance.index',     'icon' => 'heroicon-o-shield-check',        'group' => 'Hospital',            'min_level' => 4, 'is_visible' => true,  'order' => 9],
            ['label' => 'Eventos',             'route' => 'events.index',        'icon' => 'heroicon-o-calendar',            'group' => 'Hospital',            'min_level' => 4, 'is_visible' => true,  'order' => 10],
            ['label' => 'Chat',                'route' => 'chat.index',          'icon' => 'heroicon-o-chat-bubble-left-right', 'group' => 'Hospital',        'min_level' => 4, 'is_visible' => true,  'order' => 11],
            // ─── Controle de Acesso ────────────────────────────────────────
            ['label' => 'Usuários',            'route' => 'admin.usuarios.index',     'icon' => 'heroicon-o-user-group',      'group' => 'Controle de Acesso',  'min_level' => 1, 'is_visible' => true,  'order' => 1],
            ['label' => 'Papéis',              'route' => 'admin.papeis.index',       'icon' => 'heroicon-o-identification',  'group' => 'Controle de Acesso',  'min_level' => 1, 'is_visible' => true,  'order' => 2],
            ['label' => 'Permissões',          'route' => 'admin.permissoes.index',   'icon' => 'heroicon-o-key',             'group' => 'Controle de Acesso',  'min_level' => 1, 'is_visible' => true,  'order' => 3],
            ['label' => 'Vinc. Usuário',       'route' => 'admin.vinculo.index',      'icon' => 'heroicon-o-link',            'group' => 'Controle de Acesso',  'min_level' => 1, 'is_visible' => true,  'order' => 4],
            // ─── Sistema ────────────────────────────────────────────────────
            ['label' => 'Auditoria',           'route' => 'admin.sistema.auditoria',  'icon' => 'heroicon-o-clipboard-document-list', 'group' => 'Sistema',   'min_level' => 1, 'is_visible' => true,  'order' => 1],
            ['label' => 'Menus',               'route' => 'admin.sistema.menus',      'icon' => 'heroicon-o-bars-3',          'group' => 'Sistema',             'min_level' => 1, 'is_visible' => true,  'order' => 2],
            ['label' => 'Configurações',       'route' => 'admin.sistema.configuracoes', 'icon' => 'heroicon-o-cog-6-tooth', 'group' => 'Sistema',             'min_level' => 1, 'is_visible' => true,  'order' => 3],
        ];

        foreach ($items as $item) {
            MenuItem::updateOrCreate(
                ['route' => $item['route']],
                $item
            );
        }
    }
}
