# Componentes Livewire

> **Última atualização:** adicionados Grupos A (Controle de Acesso) e B (Sistema) — 10 novos componentes nas seções 17–26.

## Convenções gerais

- Toda propriedade pública sensível (como `password`) deve usar `#[Locked]` ou não ser exposta
- Validação sempre via `#[Validate]` no atributo ou `rules()` / `validate()` explícito antes de salvar
- Mensagens de sucesso via `$this->dispatch('notify', type: 'success', message: '...')`
- Componentes de tabela sempre paginam (15 itens) e usam `WithPagination`
- Lazy loading via `#[Lazy]` nos componentes pesados do dashboard

---

## 1. Dashboard\StatsCards

**Arquivo:** [app/Livewire/Dashboard/StatsCards.php](app/Livewire/Dashboard/StatsCards.php)  
**View:** [resources/views/livewire/dashboard/stats-cards.blade.php](resources/views/livewire/dashboard/stats-cards.blade.php)

**Responsabilidade:** Renderiza os 6 KPI cards do dashboard com valores do dia atual e comparativo com o dia anterior. Dados cacheados por 60 segundos para evitar queries repetidas a cada requisição Livewire.

**Propriedades públicas:**

| Prop | Tipo | Default | wire:model | Descrição |
|------|------|---------|------------|-----------|
| stats | array | [] | não | Array com todos os KPIs |

**Computed properties:**
- `getStatsProperty()` → retorna do Cache ou calcula via queries agregadas

**Métodos:**

| Método | Visibilidade | Descrição |
|--------|-------------|-----------|
| mount() | public | Carrega stats iniciais |
| refresh() | public | Limpa cache e recarrega (chamado por listener) |

**Eventos emitidos:** nenhum

**Eventos ouvidos:**

| Evento | Handler | Quem emite |
|--------|---------|-----------|
| `stats-refresh` | refresh() | PaymentModal após pagamento registrado |

**Alpine.js no template:** não

**Dependências de permissão:** `auth`, `verified` — todos os papéis veem os cards (earnings só para admin/financeiro via `@can`)

**Cache key:** `dashboard.stats.{data_de_hoje}`

---

## 2. Dashboard\DoctorOnDuty

**Arquivo:** [app/Livewire/Dashboard/DoctorOnDuty.php](app/Livewire/Dashboard/DoctorOnDuty.php)  
**View:** [resources/views/livewire/dashboard/doctor-on-duty.blade.php](resources/views/livewire/dashboard/doctor-on-duty.blade.php)

**Responsabilidade:** Carrossel com os médicos disponíveis hoje, mostrando nome, especialidade, horário de atendimento e foto. Navega entre médicos com setas esquerda/direita.

**Propriedades públicas:**

| Prop | Tipo | Default | wire:model | Descrição |
|------|------|---------|------------|-----------|
| currentIndex | int | 0 | não | Índice do médico atual no carrossel |

**Computed properties:**
- `doctors()` → Doctors disponíveis hoje (is_available = true), com user relationship

**Métodos:**

| Método | Visibilidade | Descrição |
|--------|-------------|-----------|
| mount() | public | Inicializa com index 0 |
| previous() | public | Decrementa currentIndex (com wrap) |
| next() | public | Incrementa currentIndex (com wrap) |
| currentDoctor() | public | Retorna o médico no índice atual |

**Eventos emitidos:** nenhum  
**Eventos ouvidos:** nenhum

**Alpine.js no template:** não — a navegação é feita via wire:click para simplificar. Se a performance for problema, migrar para Alpine.js puro.

**Dependências de permissão:** todos os papéis autenticados

---

## 3. Dashboard\SurveyChart

**Arquivo:** [app/Livewire/Dashboard/SurveyChart.php](app/Livewire/Dashboard/SurveyChart.php)  
**View:** [resources/views/livewire/dashboard/survey-chart.blade.php](resources/views/livewire/dashboard/survey-chart.blade.php)

**Responsabilidade:** Inicializa o gráfico de barras agrupadas "Hospital Survey" com Chart.js. Dados de pacientes gerais vs OPD (Outpatient Department) agrupados por mês.

**Propriedades públicas:**

| Prop | Tipo | Default | wire:model | Descrição |
|------|------|---------|------------|-----------|
| year | int | ano atual | sim | Ano selecionado para o gráfico |
| chartData | array | [] | não | Dados formatados para Chart.js |

**Computed properties:**
- `getChartDataProperty()` → agrupa appointments por mês e tipo para o ano selecionado

**Métodos:**

| Método | Visibilidade | Descrição |
|--------|-------------|-----------|
| mount() | public | Define year = now()->year e carrega dados |
| updatedYear() | public | Lifecycle hook: recarrega dados ao mudar o ano |

**Eventos emitidos:**

| Evento | Payload | Quem ouve |
|--------|---------|-----------|
| `chart-data-updated` | ['data' => $this->chartData] | Alpine.js no template |

**Eventos ouvidos:** nenhum

**Alpine.js no template:** sim — inicializa e atualiza o canvas Chart.js via `x-data` e `$watch` no evento `chart-data-updated`:

```js
x-data="{
    chart: null,
    init() {
        this.chart = new Chart(this.$refs.canvas, { ... });
        this.$wire.on('chart-data-updated', ({data}) => {
            this.chart.data.datasets[0].data = data.general_patient;
            this.chart.data.datasets[1].data = data.opd;
            this.chart.update();
        });
    }
}"
```

**Dependências de permissão:** `role:admin`, `role:financeiro` (outros papéis não veem o gráfico)

---

## 4. Dashboard\MiniCalendar

**Arquivo:** [app/Livewire/Dashboard/MiniCalendar.php](app/Livewire/Dashboard/MiniCalendar.php)  
**View:** [resources/views/livewire/dashboard/mini-calendar.blade.php](resources/views/livewire/dashboard/mini-calendar.blade.php)

**Responsabilidade:** Mini-calendário lateral com navegação mensal. Destaca o dia atual e os dias com agendamentos. Ao clicar em um dia, emite evento para filtrar a tabela de agendamentos.

**Propriedades públicas:**

| Prop | Tipo | Default | wire:model | Descrição |
|------|------|---------|------------|-----------|
| currentMonth | int | mês atual | não | Mês exibido (1–12) |
| currentYear | int | ano atual | não | Ano exibido |

**Computed properties:**
- `calendarDays()` → array de Carbon instances para o mês, com flag `hasAppointments`
- `daysWithAppointments()` → array de dias (int) com pelo menos 1 agendamento

**Métodos:**

| Método | Visibilidade | Descrição |
|--------|-------------|-----------|
| mount() | public | Define mês/ano para hoje |
| previousMonth() | public | Navega para o mês anterior |
| nextMonth() | public | Navega para o próximo mês |
| selectDay(int $day) | public | Emite evento com data selecionada |

**Eventos emitidos:**

| Evento | Payload | Quem ouve |
|--------|---------|-----------|
| `calendar-date-selected` | ['date' => '2026-05-12'] | `AppointmentTable` (se estiver na página) |

**Eventos ouvidos:** nenhum

**Alpine.js no template:** não

**Dependências de permissão:** todos os papéis autenticados

---

## 5. Appointments\AppointmentTable

**Arquivo:** [app/Livewire/Appointments/AppointmentTable.php](app/Livewire/Appointments/AppointmentTable.php)  
**View:** [resources/views/livewire/appointments/appointment-table.blade.php](resources/views/livewire/appointments/appointment-table.blade.php)

**Responsabilidade:** Listagem paginada de agendamentos com busca full-text, filtros por status, médico e data, e ações inline (confirmar, cancelar, ver detalhes).

**Propriedades públicas:**

| Prop | Tipo | Default | wire:model | Descrição |
|------|------|---------|------------|-----------|
| search | string | '' | `wire:model.live.debounce.400ms` | Busca por paciente ou médico |
| filterStatus | string | '' | `wire:model.live` | Filtro por status |
| filterDoctorId | string | '' | `wire:model.live` | Filtro por médico |
| filterDate | string | '' | `wire:model` | Filtro por data (date input) |
| sortBy | string | 'scheduled_at' | não | Coluna de ordenação |
| sortDir | string | 'asc' | não | Direção da ordenação |
| perPage | int | 15 | `wire:model` | Itens por página |

**Computed properties:**
- `appointments()` → paginação de `$this->perPage` com filtros e search aplicados via Eloquent scopes. Eager loads: `patient`, `doctor.user`, `room`

**Métodos:**

| Método | Visibilidade | Descrição |
|--------|-------------|-----------|
| mount() | public | Inicializa filtros do query string se presentes |
| sort(string $column) | public | Toggle de ordenação por coluna |
| confirm(string $id) | public | Muda status para 'confirmado' |
| cancel(string $id) | public | Abre modal de cancelamento |
| resetFilters() | public | Limpa todos os filtros e busca |

**Eventos emitidos:**

| Evento | Payload | Quem ouve |
|--------|---------|-----------|
| `open-cancel-modal` | ['appointmentId' => $id] | `AppointmentModal` |

**Eventos ouvidos:**

| Evento | Handler | Quem emite |
|--------|---------|-----------|
| `appointment-saved` | `$refresh` | `AppointmentForm` |
| `appointment-cancelled` | `$refresh` | `AppointmentModal` |
| `calendar-date-selected` | `setDateFilter($date)` | `MiniCalendar` |

**Alpine.js no template:** sim — dropdown de ações por linha (confirmar, editar, cancelar) com `x-show` e `@click.outside`.

**Dependências de permissão:** `can:view appointments`

**Query string:** `?status=agendado&date=2026-05-12` sincronizados via `#[Url]` nos atributos

---

## 6. Appointments\AppointmentForm

**Arquivo:** [app/Livewire/Appointments/AppointmentForm.php](app/Livewire/Appointments/AppointmentForm.php)  
**View:** [resources/views/livewire/appointments/appointment-form.blade.php](resources/views/livewire/appointments/appointment-form.blade.php)

**Responsabilidade:** Formulário de criação e edição de agendamentos. Quando `$appointmentId` é passado via mount, carrega os dados para edição. Ao salvar, invoca `CreateAppointmentAction` ou `UpdateAppointmentAction`.

**Propriedades públicas:**

| Prop | Tipo | Default | wire:model | Descrição |
|------|------|---------|------------|-----------|
| appointmentId | string|null | null | null = criação, uuid = edição |
| patientId | string | '' | `wire:model.blur` | UUID do paciente |
| doctorId | string | '' | `wire:model.live` | UUID do médico (ao mudar, carrega horários) |
| roomId | string | '' | `wire:model` | UUID da sala |
| scheduledAt | string | '' | `wire:model` | Data e hora (datetime-local input) |
| durationMinutes | int | 30 | `wire:model` | Duração em minutos |
| type | string | 'consulta' | `wire:model` | Tipo de atendimento |
| notes | string | '' | `wire:model.blur` | Observações |
| insuranceCompanyId | string | '' | `wire:model` | Convênio (opcional) |
| availableSlots | array | [] | não | Horários disponíveis ao mudar médico+data |

**Computed properties:**
- `patients()` → lista simplificada para select (id, name, cpf) — carregada no mount
- `doctors()` → médicos ativos para select
- `rooms()` → salas ativas para select
- `insurances()` → convênios ativos para select

**Métodos:**

| Método | Visibilidade | Descrição |
|--------|-------------|-----------|
| mount(string $appointmentId = null) | public | Carrega dados se edição |
| updatedDoctorId() | public | Recarrega availableSlots para o médico |
| updatedScheduledAt() | public | Recarrega availableSlots para a data |
| save() | public | Valida, chama Action, dispatch evento |

**Eventos emitidos:**

| Evento | Payload | Quem ouve |
|--------|---------|-----------|
| `appointment-saved` | ['id' => $appointment->id] | `AppointmentTable` |
| `notify` | ['type' => 'success', 'message' => '...'] | `Shared\ToastNotification` |

**Eventos ouvidos:** nenhum

**Alpine.js no template:** não

**Dependências de permissão:** `can:create appointments` (criação) / `can:update appointments` (edição)

---

## 7. Appointments\AppointmentModal

**Arquivo:** [app/Livewire/Appointments/AppointmentModal.php](app/Livewire/Appointments/AppointmentModal.php)  
**View:** [resources/views/livewire/appointments/appointment-modal.blade.php](resources/views/livewire/appointments/appointment-modal.blade.php)

**Responsabilidade:** Modal de confirmação de cancelamento de agendamento. Recebe o ID via evento, mostra dados do agendamento e campo para motivo do cancelamento.

**Propriedades públicas:**

| Prop | Tipo | Default | wire:model | Descrição |
|------|------|---------|------------|-----------|
| isOpen | bool | false | não | Visibilidade do modal |
| appointmentId | string|null | null | ID do agendamento a cancelar |
| reason | string | '' | `wire:model.blur` | Motivo do cancelamento |

**Computed properties:**
- `appointment()` → carrega o Appointment com patient e doctor quando `$appointmentId` é setado

**Métodos:**

| Método | Visibilidade | Descrição |
|--------|-------------|-----------|
| openModal(string $appointmentId) | public | Listener: seta ID e abre modal |
| closeModal() | public | Fecha modal e limpa estado |
| confirmCancel() | public | Valida motivo, chama CancelAppointmentAction |

**Eventos emitidos:**

| Evento | Payload | Quem ouve |
|--------|---------|-----------|
| `appointment-cancelled` | ['id' => $this->appointmentId] | `AppointmentTable` |
| `notify` | ['type' => 'warning', 'message' => 'Agendamento cancelado.'] | Toast |

**Eventos ouvidos:**

| Evento | Handler | Quem emite |
|--------|---------|-----------|
| `open-cancel-modal` | `openModal($appointmentId)` | `AppointmentTable` |

**Alpine.js no template:** sim — controla a transição de entrada/saída do modal:

```html
<div x-show="$wire.isOpen"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 scale-95">
```

**Dependências de permissão:** `can:cancel appointments`

---

## 8. Doctors\DoctorTable

**Arquivo:** [app/Livewire/Doctors/DoctorTable.php](app/Livewire/Doctors/DoctorTable.php)  
**View:** [resources/views/livewire/doctors/doctor-table.blade.php](resources/views/livewire/doctors/doctor-table.blade.php)

**Responsabilidade:** Listagem paginada de médicos com busca por nome, CRM ou especialidade e toggle de disponibilidade inline.

**Propriedades públicas:**

| Prop | Tipo | Default | wire:model | Descrição |
|------|------|---------|------------|-----------|
| search | string | '' | `wire:model.live.debounce.400ms` | Busca |
| filterDepartment | string | '' | `wire:model.live` | Filtro por departamento |
| filterAvailable | bool|null | null | Filtro apenas disponíveis |

**Computed properties:**
- `doctors()` → paginação 15, com `user`, `department` eager loaded

**Métodos:**

| Método | Visibilidade | Descrição |
|--------|-------------|-----------|
| toggleAvailability(string $doctorId) | public | Inverte is_available do médico |
| delete(string $doctorId) | public | Soft delete com confirmação Alpine |

**Eventos emitidos:**

| Evento | Payload | Quem ouve |
|--------|---------|-----------|
| `notify` | sucesso/erro | Toast |

**Eventos ouvidos:**

| Evento | Handler | Quem emite |
|--------|---------|-----------|
| `doctor-saved` | `$refresh` | `DoctorForm` |

**Alpine.js no template:** sim — confirmação de exclusão inline com `x-confirm` pattern:

```html
<button @click="confirm('Excluir médico?') && $wire.delete('{{ $doctor->id }}')" />
```

**Dependências de permissão:** `can:view doctors` (visualização), `can:manage doctors` (toggle e delete)

---

## 9. Doctors\DoctorForm

**Arquivo:** [app/Livewire/Doctors/DoctorForm.php](app/Livewire/Doctors/DoctorForm.php)  
**View:** [resources/views/livewire/doctors/doctor-form.blade.php](resources/views/livewire/doctors/doctor-form.blade.php)

**Responsabilidade:** Formulário de criação e edição de médicos. Inclui campos do usuário (nome, email, avatar) e dados clínicos (CRM, especialidade, disponibilidade, departamento). Cria User + Doctor em transação atômica.

**Propriedades públicas:**

| Prop | Tipo | Default | wire:model | Descrição |
|------|------|---------|------------|-----------|
| doctorId | string|null | null | null = criação |
| name | string | '' | `wire:model.blur` | Nome completo |
| email | string | '' | `wire:model.blur` | Email |
| crm | string | '' | `wire:model.blur` | CRM com UF |
| specialty | string | '' | `wire:model` | Especialidade principal |
| departmentId | string | '' | `wire:model` | Departamento |
| availableFrom | string | '08:00' | `wire:model` | Início do horário |
| availableTo | string | '17:00' | `wire:model` | Fim do horário |
| availableDays | array | [1,2,3,4,5] | não | Dias da semana |
| consultationDuration | int | 30 | `wire:model` | Duração padrão (min) |

**Métodos:**

| Método | Visibilidade | Descrição |
|--------|-------------|-----------|
| mount(?string $doctorId) | public | Carrega dados se edição |
| save() | public | Valida, chama CreateDoctorAction/UpdateDoctorAction |

**Eventos emitidos:**

| Evento | Payload | Quem ouve |
|--------|---------|-----------|
| `doctor-saved` | ['id' => $doctor->id] | `DoctorTable` |
| `notify` | sucesso | Toast |

**Alpine.js no template:** sim — checkboxes dos dias da semana gerenciados com Alpine array binding:

```html
<input type="checkbox" x-model="$wire.availableDays" value="1" />
```

**Dependências de permissão:** `can:manage doctors`

---

## 10. Patients\PatientTable

**Arquivo:** [app/Livewire/Patients/PatientTable.php](app/Livewire/Patients/PatientTable.php)  
**View:** [resources/views/livewire/patients/patient-table.blade.php](resources/views/livewire/patients/patient-table.blade.php)

**Responsabilidade:** Listagem paginada de pacientes com busca por nome, CPF ou email. Link para prontuário e histórico de consultas.

**Propriedades públicas:**

| Prop | Tipo | Default | wire:model | Descrição |
|------|------|---------|------------|-----------|
| search | string | '' | `wire:model.live.debounce.400ms` | Busca |
| filterInsurance | string | '' | `wire:model.live` | Filtro por convênio |

**Computed properties:**
- `patients()` → paginação 15 com `insuranceCompany` eager loaded

**Métodos:**

| Método | Visibilidade | Descrição |
|--------|-------------|-----------|
| delete(string $id) | public | Soft delete (com Alpine confirm) |

**Eventos ouvidos:**

| Evento | Handler | Quem emite |
|--------|---------|-----------|
| `patient-saved` | `$refresh` | `PatientForm` |

**Alpine.js no template:** sim — confirmação de exclusão

**Dependências de permissão:** `can:view patients`

---

## 11. Patients\PatientForm

**Arquivo:** [app/Livewire/Patients/PatientForm.php](app/Livewire/Patients/PatientForm.php)  
**View:** [resources/views/livewire/patients/patient-form.blade.php](resources/views/livewire/patients/patient-form.blade.php)

**Responsabilidade:** Formulário completo de paciente com tabs Alpine.js para organizar as seções: Dados pessoais, Informações médicas, Contato de emergência e Endereço.

**Propriedades públicas:**

| Prop | Tipo | Default | wire:model | Descrição |
|------|------|---------|------------|-----------|
| patientId | string|null | null | null = criação |
| name | string | '' | `wire:model.blur` | Nome completo |
| cpf | string | '' | `wire:model.blur` | CPF |
| birthdate | string | '' | `wire:model` | Data de nascimento |
| gender | string | '' | `wire:model` | M/F/O |
| email | string | '' | `wire:model.blur` | Email |
| phone | string | '' | `wire:model.blur` | Telefone |
| bloodType | string | '' | `wire:model` | Tipo sanguíneo |
| allergies | string | '' | `wire:model.lazy` | Alergias |
| emergencyContactName | string | '' | `wire:model.blur` | Contato emergência |
| emergencyContactPhone | string | '' | `wire:model.blur` | Telefone emergência |
| insuranceCompanyId | string | '' | `wire:model` | Convênio |
| insuranceCardNumber | string | '' | `wire:model.blur` | Nº da carteirinha |
| notes | string | '' | `wire:model.lazy` | Observações |

**Métodos:**

| Método | Visibilidade | Descrição |
|--------|-------------|-----------|
| mount(?string $patientId) | public | Carrega dados se edição |
| save() | public | Valida e salva via Action |

**Eventos emitidos:**

| Evento | Payload | Quem ouve |
|--------|---------|-----------|
| `patient-saved` | ['id' => $patient->id] | `PatientTable` |
| `notify` | sucesso | Toast |

**Alpine.js no template:** sim — sistema de tabs entre as seções do formulário:

```html
<div x-data="{ tab: 'pessoal' }">
    <button @click="tab = 'pessoal'" :class="{ 'active': tab === 'pessoal' }">Dados Pessoais</button>
    <div x-show="tab === 'pessoal'">...</div>
</div>
```

**Dependências de permissão:** `can:create patients` (criação) / `can:update patients` (edição)

---

## 12. Payments\PaymentModal

**Arquivo:** [app/Livewire/Payments/PaymentModal.php](app/Livewire/Payments/PaymentModal.php)  
**View:** [resources/views/livewire/payments/payment-modal.blade.php](resources/views/livewire/payments/payment-modal.blade.php)

**Responsabilidade:** Modal para registrar pagamento de uma consulta. Recebe o appointment_id via evento, pré-preenche paciente e valor, e permite selecionar forma de pagamento e aplicar desconto.

**Propriedades públicas:**

| Prop | Tipo | Default | wire:model | Descrição |
|------|------|---------|------------|-----------|
| isOpen | bool | false | não | Visibilidade |
| appointmentId | string|null | null | Consulta sendo paga |
| amount | float | 0 | `wire:model.live` | Valor bruto |
| discount | float | 0 | `wire:model.live` | Desconto |
| method | string | 'pix' | `wire:model` | Forma de pagamento |
| notes | string | '' | `wire:model.blur` | Observações |

**Computed properties:**
- `netAmount()` → amount - discount
- `appointment()` → Appointment com patient para exibir no modal

**Métodos:**

| Método | Visibilidade | Descrição |
|--------|-------------|-----------|
| openModal(string $appointmentId) | public | Listener: carrega appointment e abre |
| closeModal() | public | Fecha e limpa |
| save() | public | Valida, chama ProcessPaymentAction, dispatch eventos |

**Eventos emitidos:**

| Evento | Payload | Quem ouve |
|--------|---------|-----------|
| `payment-registered` | ['appointmentId' => $id] | Dashboard refresh |
| `stats-refresh` | — | `StatsCards` |
| `notify` | sucesso | Toast |

**Eventos ouvidos:**

| Evento | Handler | Quem emite |
|--------|---------|-----------|
| `open-payment-modal` | `openModal($id)` | `AppointmentTable` |

**Alpine.js no template:** sim — transição de entrada/saída do modal (mesmo padrão do AppointmentModal)

**Dependências de permissão:** `can:create payments`

---

## 13. Chat\ChatWindow

**Arquivo:** [app/Livewire/Chat/ChatWindow.php](app/Livewire/Chat/ChatWindow.php)  
**View:** [resources/views/livewire/chat/chat-window.blade.php](resources/views/livewire/chat/chat-window.blade.php)

**Responsabilidade:** Janela de chat entre dois usuários com polling de novas mensagens a cada 3 segundos. Mantém scroll no fundo. Marca mensagens como lidas ao abrir a conversa.

**Propriedades públicas:**

| Prop | Tipo | Default | wire:model | Descrição |
|------|------|---------|------------|-----------|
| receiverId | string | — | não | UUID do destinatário |
| newMessage | string | '' | `wire:model` | Campo de nova mensagem |

**Computed properties:**
- `messages()` → últimas 50 mensagens da conversa (sender_id + receiver_id bidirecional), ordenadas por created_at

**Métodos:**

| Método | Visibilidade | Descrição |
|--------|-------------|-----------|
| mount(string $receiverId) | public | Carrega receiver e marca como lidas |
| loadMessages() | public | Chamado pelo wire:poll — atualiza lista |
| send() | public | Valida, cria Message, limpa campo |
| markAsRead() | private | Marca como read_at todas as mensagens recebidas |

**Eventos emitidos:**

| Evento | Payload | Quem ouve |
|--------|---------|-----------|
| `scroll-to-bottom` | — | Alpine.js no template |

**Polling:** `wire:poll.3s="loadMessages"` — pausado quando aba inativa (Livewire 3 automático)

**Alpine.js no template:** sim — scroll automático para a última mensagem:

```html
<div x-data
     @scroll-to-bottom.window="$el.scrollTop = $el.scrollHeight"
     wire:key="messages-container">
```

**Dependências de permissão:** todos os papéis autenticados

---

## 14. Shared\NotificationBell

**Arquivo:** [app/Livewire/Shared/NotificationBell.php](app/Livewire/Shared/NotificationBell.php)  
**View:** [resources/views/livewire/shared/notification-bell.blade.php](resources/views/livewire/shared/notification-bell.blade.php)

**Responsabilidade:** Ícone de sino no topbar com badge de contagem de notificações não lidas. Dropdown que lista as 5 mais recentes com opção de marcar todas como lidas.

**Propriedades públicas:**

| Prop | Tipo | Default | wire:model | Descrição |
|------|------|---------|------------|-----------|
| notifications | array | [] | não | Notificações carregadas |
| unreadCount | int | 0 | não | Contador do badge |

**Métodos:**

| Método | Visibilidade | Descrição |
|--------|-------------|-----------|
| mount() | public | Carrega notificações iniciais |
| loadNotifications() | public | Chamado pelo wire:poll |
| markRead(string $id) | public | Marca notificação individual como lida |
| markAllRead() | public | Marca todas como lidas |

**Polling:** `wire:poll.30s="loadNotifications"`

**Alpine.js no template:** sim — dropdown com `x-show` e `@click.outside` para fechar:

```html
<div x-data="{ open: false }" @click.outside="open = false">
    <button @click="open = !open">...</button>
    <div x-show="open" x-transition>...</div>
</div>
```

**Dependências de permissão:** todos os papéis autenticados

---

## 27. Auth\TwoFactorChallenge

**Arquivo:** `app/Livewire/Auth/TwoFactorChallenge.php`
**View:** `resources/views/livewire/auth/two-factor-challenge.blade.php`
**Layout:** `components.layouts.guest`
**Rota:** `GET /dois-fatores` → `two-factor.challenge`
**Middleware:** `auth, check2fa` (sem `verified`)

**Descrição:** Tela de verificação TOTP exibida após login quando o usuário tem 2FA ativo e a sessão ainda não possui `auth.2fa_verified = true`. Permite alternar entre código TOTP (6 dígitos) e código de recuperação.

**Propriedades:**

| Propriedade | Tipo | Padrão | Descrição |
|-------------|------|--------|-----------|
| code | string | '' | Código TOTP de 6 dígitos |
| recovery_code | string | '' | Código de recuperação (`XXXXX-XXXXX`) |
| usingRecoveryCode | bool | false | Alterna entre os dois modos de verificação |

**Métodos:**

| Método | Descrição |
|--------|-----------|
| toggleRecoveryCode() | Alterna modo e limpa campos + erros |
| submit(Google2FA, UseRecoveryCode) | Valida o código, grava `auth.2fa_verified = true` na sessão e redireciona |

**Fluxo:** `submit()` → se `usingRecoveryCode`: chama `UseRecoveryCode::execute()` → senão: `Google2FA::verifyKey()` + `session(['auth.2fa_verified' => true])` → `redirectIntended(route('dashboard'))`.

**Alpine.js:** não utilizado (toda a lógica de toggle é Livewire).

---

## 28. Profile\UserProfile

**Arquivo:** `app/Livewire/Profile/UserProfile.php`
**View:** `resources/views/livewire/profile/user-profile.blade.php`
**Layout:** `components.layouts.app`
**Rota:** `GET /perfil` → `profile.show`
**Middleware:** `auth, check2fa, verified`

**Descrição:** Página de perfil com banner/hero, avatar, informações pessoais em leitura, links rápidos para configurações e histórico das últimas 5 ações de auditoria do próprio usuário.

**Propriedades:** nenhuma (componente somente leitura).

**Dados injetados no render():**

| Variável | Tipo | Descrição |
|----------|------|-----------|
| user | User | Usuário autenticado |
| recentActivity | Collection | Últimas 5 auditorias do próprio usuário (via `OwenIt\Auditing\Models\Audit`) |

**Seções da view:**

1. **Banner** — gradiente primary com avatar sobreposto (overflow `-mb-10`).
2. **Informações Pessoais** — grid com nome, e-mail (badge verificado), telefone, data de criação, status 2FA e status da conta.
3. **Ações Rápidas** — links para `/perfil/configuracoes` com tab pré-selecionada via query string.
4. **Atividade Recente** — lista de audits com ícone por evento (`updated` / `created`), campos modificados e `diffForHumans()`.

**Dependências de permissão:** qualquer usuário autenticado acessa apenas o próprio perfil.

---

## 29. Profile\AccountSettings

**Arquivo:** `app/Livewire/Profile/AccountSettings.php`
**View:** `resources/views/livewire/profile/account-settings.blade.php`
**Layout:** `components.layouts.app`
**Rota:** `GET /perfil/configuracoes` → `profile.settings`
**Middleware:** `auth, check2fa, verified`

**Descrição:** Página de configurações com 3 abas controladas por Alpine.js (`x-data / x-show`). A aba ativa pode ser pré-selecionada via query string `?tab=info|security|2fa`.

**Propriedades — Aba Info:**

| Propriedade | Tipo | Regra de validação |
|-------------|------|--------------------|
| name | string | required\|string\|max:255 |
| email | string | required\|email\|unique users exceto o próprio |
| phone | string | nullable\|string\|max:20 |
| avatar | UploadedFile\|null | nullable\|image\|mimes:jpeg,jpg,png,webp\|max:2048 |

**Propriedades — Aba Segurança:**

| Propriedade | Tipo | Regra |
|-------------|------|-------|
| current_password | string | required |
| password | string | required\|min:8\|confirmed |
| password_confirmation | string | — |

**Propriedades — Aba 2FA:**

| Propriedade | Tipo | Descrição |
|-------------|------|-----------|
| twoFactorSecret | string\|null | Secret em texto puro (só durante o fluxo de ativação) |
| twoFactorQrUrl | string\|null | URL usada para gerar o QR Code via BaconQrCode |
| twoFactorCode | string | Código TOTP para confirmação |
| plainRecoveryCodes | array\|null | Exibidos uma única vez após geração |
| disablePassword | string | Senha para desativar 2FA |
| disableTotpCode | string | Código TOTP para desativar 2FA |
| regenPassword | string | Senha para regenerar recovery codes |

**Métodos:**

| Método | Action chamada | Resultado |
|--------|---------------|-----------|
| saveInfo() | UpdateProfileInformation | Atualiza perfil, apaga avatar antigo, revalida e-mail se alterado |
| savePassword() | UpdatePassword | Valida senha atual e salva nova |
| enableTwoFactor() | EnableTwoFactor | Gera secret + URL do QR, exibe passo de confirmação |
| confirmTwoFactor() | ConfirmTwoFactor | Valida TOTP, salva `two_factor_confirmed_at`, exibe recovery codes |
| disableTwoFactor() | DisableTwoFactor | Requer senha + TOTP, limpa colunas 2FA |
| regenerateRecoveryCodes() | GenerateRecoveryCodes | Requer senha, gera 8 novos códigos e exibe |
| dismissRecoveryCodes() | — | Limpa `plainRecoveryCodes` da memória |

**Estados da aba 2FA:**

| Estado | Condição | UI exibida |
|--------|----------|-----------|
| Desativado | `!user->hasTwoFactorEnabled() && !twoFactorSecret` | Botão "Ativar" |
| Configurando | `twoFactorSecret && !user->hasTwoFactorEnabled()` | QR Code + campo de confirmação |
| Ativo | `user->hasTwoFactorEnabled() && !twoFactorSecret` | Banner verde + opções de regen/desativar |

**Alpine.js:** tabs via `x-show` + `@entangle('activeTab')` para sincronizar com Livewire. Seções "Gerar novos códigos" e "Desativar 2FA" usam `x-data="{ open: false }"` locais.

**Trait:** `WithFileUploads` para upload do avatar.

**QR Code:** gerado no `render()` via `BaconQrCode\Writer` com `SvgImageBackEnd` (180 px), injetado como SVG inline (`{!! $qrSvg !!}`).

---

## 30. Blade Component: topbar-user-menu

**Arquivo:** `resources/views/components/topbar-user-menu.blade.php`
**Uso:** `<x-topbar-user-menu />` no layout do painel

**Descrição:** Dropdown do avatar no topbar. Exibe foto/iniciais, nome e papel do usuário autenticado. Contém links de navegação para as páginas de perfil e toggle de dark mode.

**Links incluídos:**

| Label | Rota | Tab |
|-------|------|-----|
| Meu Perfil | `profile.show` | — |
| Configurações | `profile.settings` | — |
| Segurança | `profile.settings` | `security` |
| Dois Fatores | `profile.settings` | `2fa` |
| Modo escuro/claro | — | toggle Alpine.js + localStorage |
| Sair | `POST /logout` | — |

**Indicadores visuais:**
- Ponto verde (`.bg-emerald-500`) ao lado de "Dois Fatores" quando 2FA está ativo.
- Avatar renderizado como `<img>` se o usuário tiver foto, ou `<div>` com iniciais coloridas caso contrário.

**Alpine.js:** `x-data="{ open: false }"` com `@keydown.escape.window` e `@click.outside` para fechar. Transição `x-transition` com scale + opacity. Ícone chevron rotaciona 180° quando aberto via `:class="{ 'rotate-180': open }"`.

**Dependências de permissão:** qualquer usuário autenticado.

---

## 15. Shared\Sidebar

**Arquivo:** [app/Livewire/Shared/Sidebar.php](app/Livewire/Shared/Sidebar.php)  
**View:** [resources/views/livewire/shared/sidebar.blade.php](resources/views/livewire/shared/sidebar.blade.php)

**Responsabilidade:** Menu lateral com itens filtrados por permissão do usuário. Em mobile, colapsa via Alpine.js. Item ativo destacado baseado na rota atual.

**Propriedades públicas:** nenhuma (estado é todo Alpine.js)

**Métodos:**

| Método | Visibilidade | Descrição |
|--------|-------------|-----------|
| mount() | public | Verifica permissões do usuário atual |
| menuItems() | public | Retorna array de itens permitidos para o papel atual |

**Alpine.js no template:** sim — controle de collapse e item ativo:

```html
<nav x-data="{ open: window.innerWidth >= 1024 }"
     :class="{ 'w-64': open, 'w-0 overflow-hidden': !open }">
    @foreach ($this->menuItems() as $item)
        <a href="{{ route($item['route']) }}"
           :class="{ 'bg-green-500 text-white': '{{ request()->routeIs($item['route']) }}' }">
            ...
        </a>
    @endforeach
</nav>
```

**Dependências de permissão:** itens filtrados dinamicamente via `$this->menuItems()` baseado nas permissões do usuário

---

## 16. Shared\DarkModeToggle

**Arquivo:** [app/Livewire/Shared/DarkModeToggle.php](app/Livewire/Shared/DarkModeToggle.php)  
**View:** [resources/views/livewire/shared/dark-mode-toggle.blade.php](resources/views/livewire/shared/dark-mode-toggle.blade.php)

**Responsabilidade:** Botão de toggle entre modo claro e escuro. Persiste preferência no localStorage. Aplica/remove classe `dark` na tag `<html>`.

**Propriedades públicas:** nenhuma (puramente Alpine.js)

**Métodos:** nenhum (este componente é 100% Alpine.js, sem round-trip Livewire)

**Alpine.js no template:** sim — toda a lógica está no template:

```html
<div x-data="{
    dark: localStorage.getItem('darkMode') === 'true',
    toggle() {
        this.dark = !this.dark;
        localStorage.setItem('darkMode', this.dark);
        document.documentElement.classList.toggle('dark', this.dark);
    },
    init() {
        document.documentElement.classList.toggle('dark', this.dark);
    }
}">
    <button @click="toggle()" :aria-label="dark ? 'Ativar modo claro' : 'Ativar modo escuro'">
        <template x-if="dark"><span>☀️</span></template>
        <template x-if="!dark"><span>🌙</span></template>
    </button>
</div>
```

**Dependências de permissão:** todos os usuários

---

## GRUPO A — Controle de Acesso

---

## 17. Admin\Users\UserTable

**Arquivo:** [app/Livewire/Admin/Users/UserTable.php](app/Livewire/Admin/Users/UserTable.php)
**View:** [resources/views/livewire/admin/users/user-table.blade.php](resources/views/livewire/admin/users/user-table.blade.php)

**Responsabilidade:** Listagem paginada de usuários com busca em tempo real, filtros por papel e status, e ações inline (editar, suspender/ativar, redefinir senha, excluir soft-delete). Exibe modal com nova senha após reset.

**Propriedades públicas:**

| Prop | Tipo | Default | wire:model | Descrição |
|------|------|---------|------------|-----------|
| search | string | '' | `wire:model.live.debounce.400ms` | Busca por nome/email |
| filterRole | string | '' | `wire:model.live` | Filtro por papel Spatie |
| filterStatus | string | '' | `wire:model.live` | Filtro ativo/suspenso |
| resetPasswordFor | string|null | null | Nome do usuário cujo password foi resetado |
| newPasswordDisplay | string | '' | não | Senha nova gerada para exibir no modal |

**Métodos:**

| Método | Visibilidade | Descrição |
|--------|-------------|-----------|
| toggleStatus(string $id) | public | Inverte is_active via ToggleUserStatusAction |
| resetPassword(string $id) | public | Gera nova senha via ResetUserPasswordAction, abre modal |
| delete(string $id) | public | Soft delete via DeleteUserAction |
| exportJson() | public | Dispara download JSON de todos os usuários filtrados |

**Eventos emitidos:**

| Evento | Payload | Quem ouve |
|--------|---------|-----------|
| `notify` | type, message | Toast global |
| `open-password-modal` | — | Alpine.js na própria view |

**Eventos ouvidos:**

| Evento | Handler | Quem emite |
|--------|---------|-----------|
| `user-saved` | `refresh()` | UserForm |

**Dependências de permissão:** `users.view` (listagem), `users.edit`, `users.delete`

---

## 18. Admin\Users\UserForm

**Arquivo:** [app/Livewire/Admin/Users/UserForm.php](app/Livewire/Admin/Users/UserForm.php)
**View:** [resources/views/livewire/admin/users/user-form.blade.php](resources/views/livewire/admin/users/user-form.blade.php)

**Responsabilidade:** Formulário de criação e edição de usuários. Inclui upload de avatar (Livewire WithFileUploads), multi-select de papéis como checkboxes visuais, e toggle de status.

**Propriedades públicas:**

| Prop | Tipo | Default | wire:model | Descrição |
|------|------|---------|------------|-----------|
| userId | string|null | null | null = criação |
| name | string | '' | `wire:model.blur` | Nome |
| email | string | '' | `wire:model.blur` | Email |
| password | string | '' | `wire:model` | Apenas na criação |
| phone | string | '' | `wire:model.blur` | Telefone |
| isActive | bool | true | não (toggle) | Status |
| selectedRoles | array | [] | não | Papéis checados |
| avatar | file|null | null | `wire:model` | Upload via WithFileUploads |

**Regras de negócio:** Usuário não pode excluir a si mesmo; apenas admin cria/edita outro admin (verificado na Policy).

**Dependências de permissão:** `users.create` (criação) / `users.edit` (edição)

---

## 19. Admin\Roles\RoleTable

**Arquivo:** [app/Livewire/Admin/Roles/RoleTable.php](app/Livewire/Admin/Roles/RoleTable.php)
**View:** [resources/views/livewire/admin/roles/role-table.blade.php](resources/views/livewire/admin/roles/role-table.blade.php)

**Responsabilidade:** Listagem de papéis com contagem de permissões e usuários. Botão excluir desabilitado para papéis com usuários vinculados e protegido para o papel `admin`.

**Métodos:**

| Método | Visibilidade | Descrição |
|--------|-------------|-----------|
| delete(int $id) | public | Desvíncula permissões e exclui via DeleteRoleAction |

**Dependências de permissão:** `roles.view`, `roles.manage`

---

## 20. Admin\Roles\RoleForm

**Arquivo:** [app/Livewire/Admin/Roles/RoleForm.php](app/Livewire/Admin/Roles/RoleForm.php)
**View:** [resources/views/livewire/admin/roles/role-form.blade.php](resources/views/livewire/admin/roles/role-form.blade.php)

**Responsabilidade:** Formulário de papel com multi-select de permissões agrupadas por módulo. Campo nome bloqueado para `admin`. Nível hierárquico editável.

**Propriedades públicas:**

| Prop | Tipo | Default | wire:model | Descrição |
|------|------|---------|------------|-----------|
| roleId | int|null | null | null = criação |
| name | string | '' | `wire:model.blur` | Nome do papel |
| level | int | 99 | `wire:model` | Nível hierárquico |
| selectedPermissions | array | [] | não | Permissões marcadas |

**Dependências de permissão:** `roles.manage`

---

## 21. Admin\Permissions\PermissionTable

**Arquivo:** [app/Livewire/Admin/Permissions/PermissionTable.php](app/Livewire/Admin/Permissions/PermissionTable.php)
**View:** [resources/views/livewire/admin/permissions/permission-table.blade.php](resources/views/livewire/admin/permissions/permission-table.blade.php)

**Responsabilidade:** Listagem de permissões agrupáveis por módulo. Botão excluir desabilitado para permissões vinculadas a papéis ativos.

**Propriedades públicas:**

| Prop | Tipo | Default | wire:model | Descrição |
|------|------|---------|------------|-----------|
| filterModule | string | '' | `wire:model.live` (URL) | Filtro por módulo |

**Dependências de permissão:** `permissions.view`, `permissions.manage`

---

## 22. Admin\Permissions\PermissionForm

**Arquivo:** [app/Livewire/Admin/Permissions/PermissionForm.php](app/Livewire/Admin/Permissions/PermissionForm.php)
**View:** [resources/views/livewire/admin/permissions/permission-form.blade.php](resources/views/livewire/admin/permissions/permission-form.blade.php)

**Responsabilidade:** Formulário de criação de permissão com validação de formato `modulo.acao`. Permite criar novo módulo inline.

**Validação:** `name` deve bater com regex `/^[a-z_]+\.[a-z_]+$/`

**Dependências de permissão:** `permissions.manage`

---

## 23. Admin\UserRoles\UserRoleAssignment

**Arquivo:** [app/Livewire/Admin/UserRoles/UserRoleAssignment.php](app/Livewire/Admin/UserRoles/UserRoleAssignment.php)
**View:** [resources/views/livewire/admin/user-roles/user-role-assignment.blade.php](resources/views/livewire/admin/user-roles/user-role-assignment.blade.php)

**Responsabilidade:** Painel dois-colunas para vínculo rápido usuário↔papéis. Painel esquerdo: busca e lista de usuários. Painel direito: checkboxes dos papéis. Salva automaticamente ao marcar/desmarcar (sem botão salvar). Cada alteração dispara `AssignUserRolesAction` e invalida cache de permissões.

**Propriedades públicas:**

| Prop | Tipo | Default | wire:model | Descrição |
|------|------|---------|------------|-----------|
| search | string | '' | `wire:model.live.debounce.400ms` | Busca de usuários |
| selectedUserId | string|null | null | ID do usuário selecionado |
| currentRoles | array | [] | não | Papéis atuais do usuário selecionado |

**Alpine.js no template:** não — toda a interação é Livewire

**Dependências de permissão:** `roles.manage`

---

## GRUPO B — Sistema

---

## 24. Admin\System\AuditLog

**Arquivo:** [app/Livewire/Admin/System/AuditLog.php](app/Livewire/Admin/System/AuditLog.php)
**View:** [resources/views/livewire/admin/system/audit-log.blade.php](resources/views/livewire/admin/system/audit-log.blade.php)

**Responsabilidade:** Tela somente-leitura de logs de auditoria (owen-it). Filtros: entidade, ação (created/updated/deleted/restored), range de datas. Modal de diff lado-a-lado (Antes/Depois em JSON formatado). Exportação JSON via `response()->streamDownload()`.

**Propriedades públicas:**

| Prop | Tipo | Default | wire:model | Descrição |
|------|------|---------|------------|-----------|
| filterEntity | string | '' | não (URL) | Filtro por classe da entidade |
| filterAction | string | '' | não (URL) | Filtro por evento |
| filterFrom | string | '' | não (URL) | Data início |
| filterTo | string | '' | não (URL) | Data fim |
| showDiff | bool | false | não | Visibilidade do modal diff |
| diffOld | array|null | null | old_values do audit selecionado |
| diffNew | array|null | null | new_values do audit selecionado |

**Entidades auditadas configuradas:** User, Doctor, Patient, Appointment, Payment, Role, Permission, MenuItem, SystemSetting

**Regra:** Registros nunca deletados (sem botão de exclusão na UI).

**Dependências de permissão:** `system.audit`

---

## 25. Admin\System\MenuManager

**Arquivo:** [app/Livewire/Admin/System/MenuManager.php](app/Livewire/Admin/System/MenuManager.php)
**View:** [resources/views/livewire/admin/system/menu-manager.blade.php](resources/views/livewire/admin/system/menu-manager.blade.php)

**Responsabilidade:** Controle dinâmico da sidebar. Para cada `MenuItem`: toggle visível/oculto (salva imediatamente) e dropdown de nível mínimo por papel. Toda alteração chama `ToggleMenuVisibilityAction` que invalida o cache da sidebar para todos os níveis.

**Cache invalidado:** `sidebar.menu.all` + `sidebar.menu.level.{1..4}`

**Métodos:**

| Método | Visibilidade | Descrição |
|--------|-------------|-----------|
| toggleVisibility(int $id, bool $visible) | public | Persiste e invalida cache |
| updateMinLevel(int $id, int $level) | public | Atualiza min_level e invalida cache |

**Dependências de permissão:** `system.menus`

---

## 26. Admin\System\SystemSettings

**Arquivo:** [app/Livewire/Admin/System/SystemSettings.php](app/Livewire/Admin/System/SystemSettings.php)
**View:** [resources/views/livewire/admin/system/system-settings.blade.php](resources/views/livewire/admin/system/system-settings.blade.php)

**Responsabilidade:** Configurações dinâmicas da clínica. Cada setting renderiza o widget correto por tipo:
- `boolean` → toggle switch (salva ao clicar via `$wire.save`)
- `integer` / `decimal` → input numérico com `wire:model.blur`
- `string` → input texto com `wire:model.blur`

Flash "Salvo ✓" por 2s após cada save, gerenciado por Alpine.js via evento `flash-saved` com `$event.detail.key`.

**Propriedades públicas:**

| Prop | Tipo | Default | wire:model | Descrição |
|------|------|---------|------------|-----------|
| values | array | [] | `wire:model` por key dinâmica | Mapa key → valor tipado |

**Eventos emitidos:**

| Evento | Payload | Quem ouve |
|--------|---------|-----------|
| `flash-saved` | `['key' => $key]` | Alpine.js na view (mostra "Salvo ✓") |

**Dependências de permissão:** `system.settings`
