@extends('admin.layout.auth')
@section('title')
    Notifications Firebase
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Notifications Push Firebase</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.addons.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Notifications</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#sendNotificationModal">
                        <i class="fas fa-paper-plane me-1"></i> Envoyer Notification
                    </button>
                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#scheduleNotificationModal">
                        <i class="fas fa-clock me-1"></i> Programmer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques Notifications -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Envoyées Aujourd'hui
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $notificationStats['sent_today'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-paper-plane fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Appareils Connectés
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $notificationStats['active_devices'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-mobile-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Taux d'Ouverture
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($notificationStats['open_rate'], 1) }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-eye fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                En Attente
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $notificationStats['pending'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Historique des notifications -->
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Historique des Notifications</h6>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="filterStatus" onchange="filterNotifications()">
                            <option value="">Tous statuts</option>
                            <option value="sent">Envoyée</option>
                            <option value="delivered">Livrée</option>
                            <option value="opened">Ouverte</option>
                            <option value="failed">Échoué</option>
                        </select>
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshNotifications()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Notification</th>
                                    <th>Destinataires</th>
                                    <th>Statut</th>
                                    <th>Statistiques</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($notifications as $notification)
                                <tr>
                                    <td>
                                        <strong>{{ $notification->title }}</strong><br>
                                        <small class="text-muted">{{ Str::limit($notification->message, 50) }}</small>
                                        @if($notification->type !== 'general')
                                            <br><span class="badge badge-secondary">{{ ucfirst($notification->type) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($notification->target_type === 'all')
                                            <span class="badge badge-info">Tous les utilisateurs</span>
                                        @elseif($notification->target_type === 'customers')
                                            <span class="badge badge-success">Clients</span>
                                        @elseif($notification->target_type === 'employees')
                                            <span class="badge badge-warning">Employés</span>
                                        @else
                                            <span class="badge badge-primary">Spécifique</span>
                                        @endif
                                        <br><small class="text-muted">{{ $notification->recipient_count ?? 0 }} destinataires</small>
                                    </td>
                                    <td>
                                        @if($notification->status === 'sent')
                                            <span class="badge badge-success">Envoyée</span>
                                        @elseif($notification->status === 'scheduled')
                                            <span class="badge badge-info">Programmée</span>
                                        @elseif($notification->status === 'failed')
                                            <span class="badge badge-danger">Échouée</span>
                                        @else
                                            <span class="badge badge-warning">En attente</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($notification->status === 'sent')
                                        <div class="text-sm">
                                            <div class="text-success">✓ {{ $notification->delivered_count ?? 0 }} livrées</div>
                                            <div class="text-primary">👁 {{ $notification->opened_count ?? 0 }} ouvertes</div>
                                            @if($notification->failed_count > 0)
                                            <div class="text-danger">✗ {{ $notification->failed_count }} échouées</div>
                                            @endif
                                        </div>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($notification->scheduled_at && $notification->status === 'scheduled')
                                            <small class="text-info">
                                                Programmée:<br>
                                                {{ $notification->scheduled_at->format('d/m/Y H:i') }}
                                            </small>
                                        @else
                                            {{ $notification->created_at->format('d/m/Y H:i') }}
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary"
                                                    onclick="viewNotificationDetails({{ $notification->id }})"
                                                    title="Détails">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if($notification->status === 'scheduled')
                                            <button class="btn btn-outline-warning"
                                                    onclick="editScheduledNotification({{ $notification->id }})"
                                                    title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger"
                                                    onclick="cancelNotification({{ $notification->id }})"
                                                    title="Annuler">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            @endif
                                            @if($notification->status === 'sent')
                                            <button class="btn btn-outline-secondary"
                                                    onclick="duplicateNotification({{ $notification->id }})"
                                                    title="Dupliquer">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appareils connectés et modèles -->
        <div class="col-xl-4 col-lg-5">
            <!-- Appareils récents -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Appareils Connectés</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($recentDevices as $device)
                        <div class="list-group-item d-flex justify-content-between align-items-start border-0 px-0">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">
                                    @if($device->user)
                                        {{ $device->user->name }}
                                    @else
                                        Utilisateur inconnu
                                    @endif
                                </div>
                                <small class="text-muted">
                                    {{ $device->device_type }} • {{ $device->platform }}<br>
                                    Dernière activité: {{ $device->last_activity->diffForHumans() }}
                                </small>
                                @if($device->is_active)
                                    <span class="badge badge-success badge-sm">Actif</span>
                                @else
                                    <span class="badge badge-secondary badge-sm">Inactif</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.firebase.devices') }}" class="btn btn-sm btn-outline-primary">
                            Voir tous les appareils
                        </a>
                    </div>
                </div>
            </div>

            <!-- Modèles de notifications -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Modèles Rapides</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-success btn-sm" onclick="useTemplate('new_order')">
                            <i class="fas fa-shopping-cart me-2"></i> Nouvelle Commande
                        </button>
                        <button class="btn btn-outline-info btn-sm" onclick="useTemplate('order_ready')">
                            <i class="fas fa-check me-2"></i> Commande Prête
                        </button>
                        <button class="btn btn-outline-warning btn-sm" onclick="useTemplate('promotion')">
                            <i class="fas fa-percentage me-2"></i> Promotion
                        </button>
                        <button class="btn btn-outline-primary btn-sm" onclick="useTemplate('loyalty_reward')">
                            <i class="fas fa-gift me-2"></i> Récompense Fidélité
                        </button>
                    </div>
                </div>
            </div>

            <!-- Configuration Firebase -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Configuration</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-sm font-weight-bold">Statut Firebase:</label>
                        @if($firebaseConfig['configured'])
                            <span class="badge badge-success ms-2">Configuré</span>
                        @else
                            <span class="badge badge-danger ms-2">Non configuré</span>
                        @endif
                    </div>

                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.firebase.config') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-cogs me-2"></i> Configuration Firebase
                        </a>
                        <button class="btn btn-outline-info btn-sm" onclick="testConnection()">
                            <i class="fas fa-wifi me-2"></i> Tester Connexion
                        </button>
                        <a href="{{ route('admin.firebase.analytics') }}" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-chart-bar me-2"></i> Analytiques
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Envoi Notification -->
<div class="modal fade" id="sendNotificationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Envoyer une Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="sendNotificationForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Titre</label>
                                <input type="text" class="form-control" name="title" required maxlength="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Type</label>
                                <select class="form-select" name="type" required>
                                    <option value="general">Général</option>
                                    <option value="order">Commande</option>
                                    <option value="promotion">Promotion</option>
                                    <option value="loyalty">Fidélité</option>
                                    <option value="system">Système</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" name="message" rows="3" required maxlength="500"></textarea>
                        <div class="form-text">Maximum 500 caractères</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Destinataires</label>
                                <select class="form-select" name="target_type" required>
                                    <option value="all">Tous les utilisateurs</option>
                                    <option value="customers">Clients seulement</option>
                                    <option value="employees">Employés seulement</option>
                                    <option value="specific">Utilisateurs spécifiques</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Priorité</label>
                                <select class="form-select" name="priority">
                                    <option value="normal">Normale</option>
                                    <option value="high">Haute</option>
                                    <option value="urgent">Urgente</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div id="specificUsers" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Sélectionner les utilisateurs</label>
                            <input type="text" class="form-control" placeholder="Rechercher par nom ou email..." id="userSearch">
                            <div id="userList" class="mt-2"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Action URL (optionnel)</label>
                                <input type="url" class="form-control" name="action_url" placeholder="https://...">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Icône (optionnel)</label>
                                <input type="text" class="form-control" name="icon" placeholder="URL de l'icône">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" onclick="sendNotification()">Envoyer Maintenant</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Programmer Notification -->
<div class="modal fade" id="scheduleNotificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Programmer une Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="scheduleNotificationForm">
                    <div class="mb-3">
                        <label class="form-label">Titre</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" name="message" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" name="schedule_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Heure</label>
                                <input type="time" class="form-control" name="schedule_time" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Destinataires</label>
                        <select class="form-select" name="target_type" required>
                            <option value="all">Tous les utilisateurs</option>
                            <option value="customers">Clients seulement</option>
                            <option value="employees">Employés seulement</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-info" onclick="scheduleNotification()">Programmer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function refreshNotifications() {
    location.reload();
}

function filterNotifications() {
    const filter = document.getElementById('filterStatus').value;
    const url = new URL(window.location);
    if (filter) {
        url.searchParams.set('status', filter);
    } else {
        url.searchParams.delete('status');
    }
    window.location = url;
}

function sendNotification() {
    const form = document.getElementById('sendNotificationForm');
    const formData = new FormData(form);

    fetch('/admin/firebase/send', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Notification envoyée avec succès !');
            location.reload();
        } else {
            alert('Erreur lors de l\'envoi: ' + data.message);
        }
    })
    .catch(error => {
        alert('Erreur de connexion');
    });
}

function scheduleNotification() {
    const form = document.getElementById('scheduleNotificationForm');
    const formData = new FormData(form);

    fetch('/admin/firebase/schedule', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Notification programmée avec succès !');
            location.reload();
        } else {
            alert('Erreur lors de la programmation: ' + data.message);
        }
    })
    .catch(error => {
        alert('Erreur de connexion');
    });
}

function useTemplate(template) {
    const templates = {
        'new_order': {
            title: 'Nouvelle commande reçue',
            message: 'Une nouvelle commande vient d\'être passée et nécessite votre attention.',
            type: 'order'
        },
        'order_ready': {
            title: 'Commande prête',
            message: 'Votre commande est prête à être récupérée !',
            type: 'order'
        },
        'promotion': {
            title: 'Offre spéciale !',
            message: 'Découvrez nos nouvelles promotions exceptionnelles.',
            type: 'promotion'
        },
        'loyalty_reward': {
            title: 'Récompense débloquée !',
            message: 'Félicitations ! Vous avez débloqué une nouvelle récompense.',
            type: 'loyalty'
        }
    };

    const templateData = templates[template];
    if (templateData) {
        document.querySelector('#sendNotificationForm [name="title"]').value = templateData.title;
        document.querySelector('#sendNotificationForm [name="message"]').value = templateData.message;
        document.querySelector('#sendNotificationForm [name="type"]').value = templateData.type;

        // Ouvrir le modal
        const modal = new bootstrap.Modal(document.getElementById('sendNotificationModal'));
        modal.show();
    }
}

function viewNotificationDetails(id) {
    window.location.href = `/admin/firebase/notifications/${id}`;
}

function editScheduledNotification(id) {
    window.location.href = `/admin/firebase/notifications/${id}/edit`;
}

function cancelNotification(id) {
    if (confirm('Annuler cette notification programmée ?')) {
        fetch(`/admin/firebase/notifications/${id}/cancel`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de l\'annulation');
            }
        });
    }
}

function duplicateNotification(id) {
    fetch(`/admin/firebase/notifications/${id}/duplicate`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Pré-remplir le formulaire avec les données dupliquées
            document.querySelector('#sendNotificationForm [name="title"]').value = data.notification.title;
            document.querySelector('#sendNotificationForm [name="message"]').value = data.notification.message;
            document.querySelector('#sendNotificationForm [name="type"]').value = data.notification.type;

            const modal = new bootstrap.Modal(document.getElementById('sendNotificationModal'));
            modal.show();
        } else {
            alert('Erreur lors de la duplication');
        }
    });
}

function testConnection() {
    fetch('/admin/firebase/test-connection', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Connexion Firebase OK !');
        } else {
            alert('Erreur de connexion Firebase: ' + data.message);
        }
    })
    .catch(error => {
        alert('Erreur lors du test de connexion');
    });
}

// Gérer l'affichage des utilisateurs spécifiques
document.addEventListener('DOMContentLoaded', function() {
    const targetTypeSelect = document.querySelector('#sendNotificationForm [name="target_type"]');
    const specificUsersDiv = document.getElementById('specificUsers');

    if (targetTypeSelect) {
        targetTypeSelect.addEventListener('change', function() {
            if (this.value === 'specific') {
                specificUsersDiv.style.display = 'block';
            } else {
                specificUsersDiv.style.display = 'none';
            }
        });
    }
});
</script>
@endpush
