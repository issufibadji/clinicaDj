# Design System — app-clinica-jm

## 6.1 Paleta de cores

### Justificativa da escolha
O verde (#10B981 / Emerald-500 do Tailwind) transmite saúde, confiança e vitalidade — ideal para contexto médico. O âmbar (#F59E0B) é usado como alerta secundário (OPD, pendências) sem conotação negativa. O slate-800 para a sidebar cria contraste profissional sem ser tão pesado quanto o preto puro.

### Tokens de cor (tailwind.config.js)

```js
// tailwind.config.js
theme: {
    extend: {
        colors: {
            primary: {
                50:  '#ecfdf5',
                100: '#d1fae5',
                200: '#a7f3d0',
                300: '#6ee7b7',
                400: '#34d399',
                500: '#10b981',  // COR PRINCIPAL — botões, badges, links ativos
                600: '#059669',
                700: '#047857',
                800: '#065f46',
                900: '#064e3b',
            },
            secondary: {
                300: '#fcd34d',
                400: '#fbbf24',
                500: '#f59e0b',  // COR SECUNDÁRIA — OPD, alertas, destaques
                600: '#d97706',
                700: '#b45309',
            },
            sidebar: {
                bg:     '#1e293b',  // slate-800 — fundo da sidebar
                hover:  '#334155',  // slate-700 — hover dos itens
                active: '#10b981',  // primary-500 — item ativo
                text:   '#94a3b8',  // slate-400 — texto dos itens inativos
                'text-active': '#ffffff',  // branco — texto do item ativo
            },
            surface: {
                DEFAULT: '#ffffff',        // card e modal (light)
                dark:    '#1e293b',        // card e modal (dark)
                bg:      '#f1f5f9',        // fundo da página (light) — slate-100
                'bg-dark': '#0f172a',      // fundo da página (dark) — slate-900
            },
            status: {
                agendado:       '#3b82f6',  // blue-500
                confirmado:     '#10b981',  // primary-500
                em_atendimento: '#f59e0b',  // secondary-500
                realizado:      '#6b7280',  // gray-500
                cancelado:      '#ef4444',  // red-500
                falta:          '#8b5cf6',  // violet-500
            },
        },
    },
}
```

### Variáveis CSS (resources/css/app.css)

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
    :root {
        --color-primary:   10 185 129;   /* #10b981 em RGB para opacity helpers */
        --color-secondary: 245 158 11;
        --color-sidebar:   30 41 59;
        --color-surface:   255 255 255;
        --color-bg:        241 245 249;
    }

    .dark {
        --color-surface: 30 41 59;
        --color-bg:      15 23 42;
    }
}
```

---

## 6.2 Tipografia

### Fonte escolhida: **Inter**

**Justificativa:** Inter é projetada especificamente para interfaces digitais de alta densidade de informação. Tem excelente legibilidade em tamanhos pequenos (12px–14px), usado frequentemente em dashboards médicos e administrativos. É gratuita via Google Fonts e tem suporte completo a caracteres latinos com diacríticos (ç, ã, é).

**Alternativa descartada:** Nunito foi considerada (mais arredondada, amigável) mas fica juvenil para um painel médico profissional.

### Configuração (resources/views/layouts/app.blade.php)

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
```

```js
// tailwind.config.js
fontFamily: {
    sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'sans-serif'],
}
```

### Escala de tamanhos

| Classe Tailwind | Tamanho | Peso | Uso |
|----------------|---------|------|-----|
| `text-xs` | 12px | 400/500 | Labels de badge, hint text, cabeçalhos de tabela |
| `text-sm` | 14px | 400 | Corpo de texto padrão, células de tabela, labels de input |
| `text-base` | 16px | 400/500 | Parágrafos, texto de cards |
| `text-lg` | 18px | 500/600 | Títulos de seção, valores de KPI secundários |
| `text-xl` | 20px | 600 | Títulos de página |
| `text-2xl` | 24px | 700 | Valores principais de KPI cards |
| `text-3xl` | 30px | 700 | Earnings/valores monetários destacados |

### Pesos usados

| Classe | Peso | Uso |
|--------|------|-----|
| `font-normal` | 400 | Corpo de texto geral |
| `font-medium` | 500 | Labels, itens de menu, células importantes |
| `font-semibold` | 600 | Títulos de cards, cabeçalhos de seção |
| `font-bold` | 700 | Valores de KPI, totais financeiros |

---

## 6.3 Componentes base (classes Tailwind reutilizáveis)

### Card padrão

```blade
{{-- resources/views/components/card.blade.php --}}
<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-6']) }}>
    {{ $slot }}
</div>
```

Uso: `<x-card class="mt-4">...</x-card>`

---

### KPI Card

```blade
{{-- resources/views/components/kpi-card.blade.php --}}
@props(['label', 'value', 'previous' => null, 'icon', 'color' => 'primary'])

<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-5 flex items-center justify-between">
    <div>
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">
            {{ $label }}
        </p>
        <p class="text-2xl font-bold text-slate-800 dark:text-white mt-1">
            {{ $value }}
        </p>
        @if ($previous)
            <p class="text-xs text-slate-400 mt-1">{{ $previous }}</p>
        @endif
    </div>
    <div class="w-12 h-12 rounded-full bg-{{ $color }}-100 dark:bg-{{ $color }}-900/30 flex items-center justify-center">
        {{ $icon }}
    </div>
</div>
```

Uso:
```blade
<x-kpi-card
    label="Appointments"
    value="40"
    previous="Yesterday 32 Appointments"
    color="primary">
    <x-slot:icon>
        <x-heroicon-o-calendar class="w-6 h-6 text-primary-500" />
    </x-slot:icon>
</x-kpi-card>
```

---

### Botões

```html
<!-- Primário -->
<button class="inline-flex items-center gap-2 px-4 py-2 bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
    Salvar
</button>

<!-- Secundário (outline) -->
<button class="inline-flex items-center gap-2 px-4 py-2 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2">
    Cancelar
</button>

<!-- Danger -->
<button class="inline-flex items-center gap-2 px-4 py-2 bg-red-500 hover:bg-red-600 active:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
    Excluir
</button>

<!-- Ghost (ação discreta) -->
<button class="inline-flex items-center gap-2 px-3 py-1.5 text-slate-600 dark:text-slate-400 hover:text-primary-500 text-sm font-medium rounded-lg hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
    Ver detalhes
</button>
```

---

### Badge de status

```blade
{{-- resources/views/components/badge.blade.php --}}
@props(['status'])

@php
$classes = match($status) {
    'agendado'       => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
    'confirmado'     => 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300',
    'em_atendimento' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
    'realizado'      => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
    'cancelado'      => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
    'falta'          => 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300',
    default          => 'bg-slate-100 text-slate-600',
};

$labels = [
    'agendado'       => 'Agendado',
    'confirmado'     => 'Confirmado',
    'em_atendimento' => 'Em atendimento',
    'realizado'      => 'Realizado',
    'cancelado'      => 'Cancelado',
    'falta'          => 'Falta',
];
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $classes }}">
    {{ $labels[$status] ?? $status }}
</span>
```

---

### Inputs

```html
<!-- Input texto padrão -->
<div class="space-y-1">
    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
        Nome do paciente
    </label>
    <input
        type="text"
        class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm px-3 py-2 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
        placeholder="Digite o nome..."
    />
    <!-- Erro de validação -->
    <p class="text-xs text-red-600 dark:text-red-400">Campo obrigatório.</p>
</div>

<!-- Select -->
<select class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
    <option value="">Selecione...</option>
</select>

<!-- Textarea -->
<textarea
    rows="4"
    class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm px-3 py-2 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors resize-none">
</textarea>
```

---

### Tabela com striped e hover

```html
<div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
    <table class="w-full text-sm text-left">
        <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
                <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide text-xs">
                    Paciente
                </th>
                <!-- mais colunas -->
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            <tr class="bg-white dark:bg-slate-800 hover:bg-primary-50 dark:hover:bg-slate-700/50 transition-colors">
                <td class="px-4 py-3 text-slate-800 dark:text-slate-200">João Silva</td>
            </tr>
            <!-- striped: adicionar odd:bg-slate-50 dark:odd:bg-slate-800/50 no <tr> -->
        </tbody>
    </table>
</div>
```

---

### Modal (Alpine.js + Livewire)

```html
<!-- Overlay + modal — padrão usado em todos os modais da aplicação -->
<div
    x-show="$wire.isOpen"
    x-transition:enter="ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    role="dialog" aria-modal="true">

    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="$wire.closeModal()"></div>

    <!-- Painel -->
    <div
        x-show="$wire.isOpen"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        class="relative z-10 w-full max-w-lg bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-6">

        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Título do Modal</h2>
            <button @click="$wire.closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>

        <!-- Conteúdo -->
        <div class="space-y-4">{{ $slot }}</div>

        <!-- Ações -->
        <div class="flex justify-end gap-3 mt-6">
            <button @click="$wire.closeModal()" class="...">Cancelar</button>
            <button wire:click="save" class="...">Confirmar</button>
        </div>
    </div>
</div>
```

---

### Alert / Flash message

```html
<!-- Toast / flash — injetado pelo componente Shared\ToastNotification -->
<!-- x-data gerencia fila de notificações com auto-dismiss em 4s -->
<div
    x-data="toastManager()"
    @notify.window="add($event.detail)"
    class="fixed top-4 right-4 z-[60] space-y-2 w-80">

    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="toast.visible"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            :class="{
                'border-l-4 border-primary-500 bg-white dark:bg-slate-800': toast.type === 'success',
                'border-l-4 border-amber-500 bg-white dark:bg-slate-800': toast.type === 'warning',
                'border-l-4 border-red-500 bg-white dark:bg-slate-800': toast.type === 'error',
            }"
            class="flex items-start gap-3 p-4 rounded-lg shadow-lg">
            <p class="text-sm text-slate-700 dark:text-slate-200 flex-1" x-text="toast.message"></p>
            <button @click="remove(toast.id)" class="text-slate-400 hover:text-slate-600">×</button>
        </div>
    </template>
</div>

<script>
function toastManager() {
    return {
        toasts: [],
        add({ type = 'success', message }) {
            const id = Date.now();
            this.toasts.push({ id, type, message, visible: true });
            setTimeout(() => this.remove(id), 4000);
        },
        remove(id) {
            const toast = this.toasts.find(t => t.id === id);
            if (toast) toast.visible = false;
            setTimeout(() => {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }, 300);
        }
    }
}
</script>
```

---

### Breadcrumb

```html
<nav aria-label="Breadcrumb" class="flex items-center gap-1.5 text-sm">
    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-primary-500 transition-colors">
        Dashboard
    </a>
    <x-heroicon-o-chevron-right class="w-4 h-4 text-slate-300" />
    <a href="{{ route('patients.index') }}" class="text-slate-400 hover:text-primary-500 transition-colors">
        Pacientes
    </a>
    <x-heroicon-o-chevron-right class="w-4 h-4 text-slate-300" />
    <span class="text-slate-700 dark:text-slate-200 font-medium">João Silva</span>
</nav>
```

---

### Paginação

O Livewire usa a view de paginação padrão do Laravel. Sobrescrever com uma view customizada que usa classes Tailwind:

```bash
php artisan livewire:publish --pagination
```

Arquivo: `resources/views/vendor/livewire/tailwind.blade.php`

Aparência: botões Previous/Next arredondados, números de página com highlight no ativo em `primary-500`.

---

## 6.4 Layout base

```
┌─────────────────────────────────────────────────────────────────┐
│  TOPBAR (h-16, bg-white, border-b, shadow-sm, fixed top-0)      │
│  [≡ Toggle]  [Search Bar]       [🔔 Bell] [Avatar ▼] [Dark🌙]  │
└─────────────────────────────────────────────────────────────────┘
│                                                                   │
│  SIDEBAR (w-64, bg-slate-800, fixed left-0, h-screen)            │
│  ┌──────────────────────────────────────────────────────────────┐│
│  │  🏥 app-clinica-jm                            (logo/brand)  ││
│  │  ─────────────────────                                       ││
│  │  Hospital                                (section label)    ││
│  │  ▶ Dashboard        ← active (bg-primary-500, text-white)   ││
│  │    Appointments      ← inactive (text-slate-400)            ││
│  │    Doctors                                                   ││
│  │    Patients                                                  ││
│  │    Room Allotments                                           ││
│  │    Payments                                                  ││
│  │    Expenses Report                                           ││
│  │    Departments                                               ││
│  │    Insurance Company                                         ││
│  │    Events                                                    ││
│  │    Chat                                                      ││
│  └──────────────────────────────────────────────────────────────┘│
│                                                                   │
│  MAIN CONTENT (ml-64, mt-16, p-6, bg-slate-100)                  │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │  Breadcrumb + Page Title                                    │ │
│  │  ─────────────────────                                      │ │
│  │  [Content Area — Livewire components]                       │ │
│  └─────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

### Grid do Dashboard

```
┌─────────────────────────────────────────────────────────────────┐
│  [ Today Available Card — 1/3 largura ]  [ KPI Grid — 2/3 ]    │
│                                          [ Appt | Admit | Ops ] │
│                                          [ Doctors | Nurses | $ ]│
├─────────────────────────────────────────┬───────────────────────┤
│  Hospital Survey Chart (2/3 largura)    │ Mini Calendar (1/3)   │
└─────────────────────────────────────────┴───────────────────────┘
```

---

## 6.5 Sidebar

- **Largura:** `w-64` (256px) em desktop (≥ 1024px)
- **Colapsada:** `w-0 overflow-hidden` em mobile (< 1024px), toggle via Alpine
- **Background:** `bg-sidebar-bg` (`#1E293B`)
- **Logo:** área de 64px de altura com nome da clínica e ícone de monitor cardíaco
- **Seção:** label "Hospital" em `text-xs uppercase tracking-widest text-slate-500`
- **Item inativo:** `text-slate-400 hover:bg-sidebar-hover hover:text-white`
- **Item ativo:** `bg-primary-500 text-white` (destaque verde)
- **Ícones:** Heroicons outline (20px), posicionados à esquerda do label
- **Espaçamento:** `py-2 px-4 rounded-lg mx-2` em cada item
- **Transição de collapse:**

```html
<nav x-data="{ sidebarOpen: window.innerWidth >= 1024 }"
     :class="sidebarOpen ? 'w-64' : 'w-0 lg:w-64'"
     class="transition-all duration-300 overflow-hidden flex-shrink-0 h-screen bg-slate-800 fixed left-0 top-0 z-30">
```

---

## 6.6 Dark mode

**Strategy:** classe `dark` adicionada/removida na tag `<html>` via Alpine.js. Tailwind usa `darkMode: 'class'` no config.

```js
// tailwind.config.js
darkMode: 'class',
```

**Inicialização (layout app.blade.php)** — antes do `<body>` para evitar flash of unstyled content:

```html
<script>
    if (localStorage.getItem('darkMode') === 'true' ||
        (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    }
</script>
```

**Convenção de uso:**

| Elemento | Light | Dark |
|---------|-------|------|
| Fundo da página | `bg-slate-100` | `dark:bg-slate-900` |
| Card | `bg-white` | `dark:bg-slate-800` |
| Texto principal | `text-slate-800` | `dark:text-white` |
| Texto secundário | `text-slate-500` | `dark:text-slate-400` |
| Borda | `border-slate-200` | `dark:border-slate-700` |
| Input background | `bg-white` | `dark:bg-slate-700` |
| Topbar | `bg-white` | `dark:bg-slate-800` |

---

## 6.7 Ícones

**Biblioteca escolhida: Heroicons** via Blade Components

**Justificativa:** Heroicons é feito pela equipe Tailwind Labs, tem estilo consistente com o design system Tailwind, disponível em dois estilos (outline e solid), e o pacote `blade-heroicons` permite uso via `<x-heroicon-o-*>` e `<x-heroicon-s-*>` sem CDN.

**Instalação:**
```bash
composer require blade-ui-kit/blade-heroicons
```

**Padrão de uso:**

```blade
{{-- Outline (uso geral, itens de menu, ações) --}}
<x-heroicon-o-calendar class="w-5 h-5" />
<x-heroicon-o-users class="w-5 h-5" />
<x-heroicon-o-currency-dollar class="w-5 h-5" />

{{-- Solid (badges, ícones de KPI cards — mais peso visual) --}}
<x-heroicon-s-heart class="w-6 h-6 text-primary-500" />
```

**Mapeamento sidebar:**

| Item | Ícone |
|------|-------|
| Dashboard | `heroicon-o-squares-2x2` |
| Appointments | `heroicon-o-calendar-days` |
| Doctors | `heroicon-o-user-circle` |
| Patients | `heroicon-o-users` |
| Room Allotments | `heroicon-o-building-office` |
| Payments | `heroicon-o-banknotes` |
| Expenses Report | `heroicon-o-chart-bar` |
| Departments | `heroicon-o-building-office-2` |
| Insurance Company | `heroicon-o-shield-check` |
| Events | `heroicon-o-calendar` |
| Chat | `heroicon-o-chat-bubble-left-right` |

---

## 6.8 Animações e transições

### Livewire wire:loading

Usado para feedback visual durante requests Livewire:

```html
<!-- Spinner no botão durante submit -->
<button wire:click="save" class="...">
    <span wire:loading.remove wire:target="save">Salvar</span>
    <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.37 0 0 5.37 0 12h4z"/>
        </svg>
        Salvando...
    </span>
</button>

<!-- Overlay na tabela durante filtros -->
<div wire:loading.class="opacity-50 pointer-events-none" wire:target="search,filterStatus">
    <!-- tabela -->
</div>
```

### Alpine x-transition — Modais e Dropdowns

```html
<!-- Dropdown padrão (NotificationBell, menus de ação) -->
<div
    x-show="open"
    x-transition:enter="transition ease-out duration-100"
    x-transition:enter-start="transform opacity-0 scale-95"
    x-transition:enter-end="transform opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-75"
    x-transition:leave-start="transform opacity-100 scale-100"
    x-transition:leave-end="transform opacity-0 scale-95"
    class="absolute right-0 mt-2 w-72 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-100 dark:border-slate-700 z-40">
```

### Skeleton loading para tabelas

```html
<!-- Exibido enquanto Livewire carrega com wire:loading -->
<template wire:loading wire:target="search,filterStatus,filterDate">
    @for ($i = 0; $i < 5; $i++)
        <tr class="animate-pulse">
            <td class="px-4 py-3"><div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-3/4"></div></td>
            <td class="px-4 py-3"><div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-1/2"></div></td>
            <td class="px-4 py-3"><div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-2/3"></div></td>
            <td class="px-4 py-3"><div class="h-6 bg-slate-200 dark:bg-slate-700 rounded-full w-20"></div></td>
        </tr>
    @endfor
</template>
```

### Transição de página (Livewire navigate)

Se habilitado o `wire:navigate` para navegação SPA-like:

```blade
{{-- layouts/app.blade.php --}}
<x-slot:head>
    @livewireStyles
</x-slot:head>

{{-- Todas as âncoras internas usam wire:navigate --}}
<a href="{{ route('appointments.index') }}" wire:navigate>Agendamentos</a>
```

O Livewire 3 gerencia automaticamente a transição de conteúdo sem reload de página inteira.
