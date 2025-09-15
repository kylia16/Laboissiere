// La Boissière Management System - Main JavaScript File

class LaBoissiere {
    constructor() {
        this.currentSection = 'dashboard';
        this.clients = JSON.parse(localStorage.getItem('clients')) || [];
        this.reservations = JSON.parse(localStorage.getItem('reservations')) || [];
        this.products = JSON.parse(localStorage.getItem('products')) || [];
        this.sales = JSON.parse(localStorage.getItem('sales')) || [];
        this.currentDate = new Date();
        this.calendarView = 'month';

        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadDashboard();
        this.initializeData();
    }

    setupEventListeners() {
        // Navigation
        document.querySelectorAll('.nav-link[data-section]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const section = e.target.getAttribute('data-section');
                this.showSection(section);
            });
        });

        // Client form
        document.getElementById('save-client').addEventListener('click', () => {
            this.saveClient();
        });

        // Client search
        document.getElementById('client-search').addEventListener('input', (e) => {
            this.searchClients(e.target.value);
        });

        // Calendar navigation
        document.getElementById('prev-period').addEventListener('click', () => {
            this.navigateCalendar(-1);
        });

        document.getElementById('next-period').addEventListener('click', () => {
            this.navigateCalendar(1);
        });

        // Calendar view toggle
        document.getElementById('view-month').addEventListener('click', () => {
            this.setCalendarView('month');
        });

        document.getElementById('view-week').addEventListener('click', () => {
            this.setCalendarView('week');
        });
    }

    showSection(sectionName) {
        // Hide all sections
        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.remove('active');
        });

        // Show selected section
        document.getElementById(`${sectionName}-section`).classList.add('active');

        // Update navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        document.querySelector(`[data-section="${sectionName}"]`).classList.add('active');

        this.currentSection = sectionName;

        // Load section-specific data
        switch (sectionName) {
            case 'dashboard':
                this.loadDashboard();
                break;
            case 'calendar':
                this.loadCalendar();
                break;
            case 'clients':
                this.loadClients();
                break;
        }
    }

    // Dashboard Methods
    loadDashboard() {
        this.updateStats();
        this.loadRecentActivity();
        this.loadTodayArrivals();
    }

    updateStats() {
        const currentMonth = new Date().getMonth();
        const currentYear = new Date().getFullYear();

        // Monthly reservations
        const monthlyReservations = this.reservations.filter(res => {
            const resDate = new Date(res.dateArrivee);
            return resDate.getMonth() === currentMonth && resDate.getFullYear() === currentYear;
        }).length;

        document.getElementById('monthly-reservations').textContent = monthlyReservations;

        // Monthly revenue
        const monthlyRevenue = this.reservations
            .filter(res => {
                const resDate = new Date(res.dateArrivee);
                return resDate.getMonth() === currentMonth && resDate.getFullYear() === currentYear;
            })
            .reduce((total, res) => total + (res.montantTotal || 0), 0);

        document.getElementById('monthly-revenue').textContent = `${monthlyRevenue}€`;

        // Occupied rooms today
        const today = new Date().toISOString().split('T')[0];
        const occupiedToday = this.reservations.filter(res => {
            return res.dateArrivee <= today && res.dateDepart > today && res.statut === 'confirmee';
        }).length;

        document.getElementById('occupied-rooms').textContent = `${occupiedToday}/5`;

        // New clients this month
        const newClients = this.clients.filter(client => {
            const clientDate = new Date(client.dateCreation);
            return clientDate.getMonth() === currentMonth && clientDate.getFullYear() === currentYear;
        }).length;

        document.getElementById('new-clients').textContent = newClients;
    }

    loadRecentActivity() {
        const activityContainer = document.getElementById('recent-activity');

        // Simulate recent activity
        const activities = [
            { type: 'reservation', message: 'Nouvelle réservation - Chambre Lavande', time: '2h', icon: 'bed', color: 'success' },
            { type: 'payment', message: 'Paiement reçu - 150€', time: '4h', icon: 'euro-sign', color: 'info' },
            { type: 'client', message: 'Nouveau client ajouté', time: '6h', icon: 'user-plus', color: 'primary' }
        ];

        if (activities.length === 0) {
            activityContainer.innerHTML = '<p class="text-muted">Aucune activité récente</p>';
            return;
        }

        activityContainer.innerHTML = activities.map(activity => `
            <div class="activity-item d-flex align-items-center">
                <div class="activity-icon bg-${activity.color} text-white">
                    <i class="fas fa-${activity.icon}"></i>
                </div>
                <div class="flex-grow-1">
                    <div>${activity.message}</div>
                    <small class="activity-time">Il y a ${activity.time}</small>
                </div>
            </div>
        `).join('');
    }

    loadTodayArrivals() {
        const arrivalsContainer = document.getElementById('today-arrivals');
        const today = new Date().toISOString().split('T')[0];

        const todayArrivals = this.reservations.filter(res =>
            res.dateArrivee === today && res.statut === 'confirmee'
        );

        if (todayArrivals.length === 0) {
            arrivalsContainer.innerHTML = '<p class="text-muted">Aucune arrivée prévue</p>';
            return;
        }

        arrivalsContainer.innerHTML = todayArrivals.map(arrival => {
            const client = this.clients.find(c => c.id === arrival.clientId);
            return `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <strong>${client ? client.nom + ' ' + client.prenom : 'Client inconnu'}</strong><br>
                        <small class="text-muted">Chambre ${arrival.chambre}</small>
                    </div>
                    <span class="badge bg-success">${arrival.heureArrivee || '15:00'}</span>
                </div>
            `;
        }).join('');
    }

    // Calendar Methods
    loadCalendar() {
        this.updateCalendarPeriod();
        this.renderCalendar();
    }

    setCalendarView(view) {
        this.calendarView = view;

        // Update buttons
        document.querySelectorAll('#view-month, #view-week').forEach(btn => {
            btn.classList.remove('active');
        });
        document.getElementById(`view-${view}`).classList.add('active');

        this.loadCalendar();
    }

    navigateCalendar(direction) {
        if (this.calendarView === 'month') {
            this.currentDate.setMonth(this.currentDate.getMonth() + direction);
        } else {
            this.currentDate.setDate(this.currentDate.getDate() + (direction * 7));
        }
        this.loadCalendar();
    }

    updateCalendarPeriod() {
        const periodElement = document.getElementById('current-period');

        if (this.calendarView === 'month') {
            const monthNames = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
            ];
            periodElement.textContent = `${monthNames[this.currentDate.getMonth()]} ${this.currentDate.getFullYear()}`;
        } else {
            // Week view logic
            const startOfWeek = new Date(this.currentDate);
            startOfWeek.setDate(this.currentDate.getDate() - this.currentDate.getDay() + 1);
            const endOfWeek = new Date(startOfWeek);
            endOfWeek.setDate(startOfWeek.getDate() + 6);

            periodElement.textContent = `${startOfWeek.getDate()}/${startOfWeek.getMonth() + 1} - ${endOfWeek.getDate()}/${endOfWeek.getMonth() + 1}`;
        }
    }

    renderCalendar() {
        const container = document.getElementById('calendar-container');

        if (this.calendarView === 'month') {
            this.renderMonthCalendar(container);
        } else {
            this.renderWeekCalendar(container);
        }
    }

    renderMonthCalendar(container) {
            const year = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth();
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const startDate = new Date(firstDay);
            startDate.setDate(startDate.getDate() - firstDay.getDay() + 1);

            const dayNames = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];

            let html = '<div class="calendar-grid">';

            // Header
            dayNames.forEach(day => {
                html += `<div class="calendar-header">${day}</div>`;
            });

            // Days
            const currentDate = new Date(startDate);
            for (let week = 0; week < 6; week++) {
                for (let day = 0; day < 7; day++) {
                    const isCurrentMonth = currentDate.getMonth() === month;
                    const isToday = this.isToday(currentDate);
                    const dateStr = currentDate.toISOString().split('T')[0];

                    const dayReservations = this.reservations.filter(res =>
                        res.dateArrivee <= dateStr && res.dateDepart > dateStr
                    );

                    html += `
                    <div class="calendar-day ${!isCurrentMonth ? 'other-month' : ''} ${isToday ? 'today' : ''}" 
                         data-date="${dateStr}">
                        <div class="calendar-day-number">${currentDate.getDate()}</div>
                        ${dayReservations.map(res => {
                            const client = this.clients.find(c => c.id === res.clientId);
                            return `<div class="reservation-block ${res.statut === 'en-attente' ? 'pending' : res.statut === 'annulee' ? 'cancelled' : ''}">
                                ${client ? client.nom : 'Réservation'} - ${res.chambre}
                            </div>`;
                        }).join('')}
                    </div>
                `;
                
                currentDate.setDate(currentDate.getDate() + 1);
            }
        }
        
        html += '</div>';
        container.innerHTML = html;
    }

    renderWeekCalendar(container) {
        // Week calendar implementation
        container.innerHTML = '<p class="text-center">Vue semaine en cours de développement...</p>';
    }

    isToday(date) {
        const today = new Date();
        return date.toDateString() === today.toDateString();
    }

    // Client Methods
    loadClients() {
        this.renderClientsTable();
    }

    renderClientsTable() {
        const tbody = document.querySelector('#clients-table tbody');
        
        if (this.clients.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Aucun client enregistré</td></tr>';
            return;
        }
        
        tbody.innerHTML = this.clients.map(client => `
            <tr>
                <td><strong>${client.nom} ${client.prenom}</strong></td>
                <td>${client.email}</td>
                <td>${client.telephone || '-'}</td>
                <td>${client.ville || '-'}</td>
                <td>${client.derniereVisite ? new Date(client.derniereVisite).toLocaleDateString() : 'Jamais'}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="app.editClient(${client.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="app.deleteClient(${client.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    saveClient() {
        const form = document.getElementById('client-form');
        const formData = new FormData(form);
        
        const client = {
            id: Date.now(),
            nom: document.getElementById('client-nom').value,
            prenom: document.getElementById('client-prenom').value,
            email: document.getElementById('client-email').value,
            telephone: document.getElementById('client-telephone').value,
            adresse: document.getElementById('client-adresse').value,
            codePostal: document.getElementById('client-code-postal').value,
            ville: document.getElementById('client-ville').value,
            origine: document.getElementById('client-origine').value,
            notes: document.getElementById('client-notes').value,
            dateCreation: new Date().toISOString(),
            derniereVisite: null
        };
        
        // Validation
        if (!client.nom || !client.prenom || !client.email) {
            alert('Veuillez remplir tous les champs obligatoires');
            return;
        }
        
        // Check if email already exists
        if (this.clients.some(c => c.email === client.email)) {
            alert('Un client avec cette adresse email existe déjà');
            return;
        }
        
        this.clients.push(client);
        this.saveToStorage('clients', this.clients);
        
        // Close modal and refresh table
        const modal = bootstrap.Modal.getInstance(document.getElementById('clientModal'));
        modal.hide();
        form.reset();
        
        this.renderClientsTable();
        this.showSuccessMessage('Client ajouté avec succès');
    }

    editClient(clientId) {
        const client = this.clients.find(c => c.id === clientId);
        if (!client) return;
        
        // Fill form with client data
        document.getElementById('client-nom').value = client.nom;
        document.getElementById('client-prenom').value = client.prenom;
        document.getElementById('client-email').value = client.email;
        document.getElementById('client-telephone').value = client.telephone || '';
        document.getElementById('client-adresse').value = client.adresse || '';
        document.getElementById('client-code-postal').value = client.codePostal || '';
        document.getElementById('client-ville').value = client.ville || '';
        document.getElementById('client-origine').value = client.origine || '';
        document.getElementById('client-notes').value = client.notes || '';
        
        // Change modal title and save button
        document.querySelector('#clientModal .modal-title').textContent = 'Modifier le client';
        document.getElementById('save-client').textContent = 'Mettre à jour';
        document.getElementById('save-client').setAttribute('data-client-id', clientId);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('clientModal'));
        modal.show();
    }

    deleteClient(clientId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce client ?')) {
            this.clients = this.clients.filter(c => c.id !== clientId);
            this.saveToStorage('clients', this.clients);
            this.renderClientsTable();
            this.showSuccessMessage('Client supprimé avec succès');
        }
    }

    searchClients(query) {
        const filteredClients = this.clients.filter(client => 
            client.nom.toLowerCase().includes(query.toLowerCase()) ||
            client.prenom.toLowerCase().includes(query.toLowerCase()) ||
            client.email.toLowerCase().includes(query.toLowerCase())
        );
        
        this.renderFilteredClients(filteredClients);
    }

    renderFilteredClients(clients) {
        const tbody = document.querySelector('#clients-table tbody');
        
        if (clients.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Aucun client trouvé</td></tr>';
            return;
        }
        
        tbody.innerHTML = clients.map(client => `
            <tr>
                <td><strong>${client.nom} ${client.prenom}</strong></td>
                <td>${client.email}</td>
                <td>${client.telephone || '-'}</td>
                <td>${client.ville || '-'}</td>
                <td>${client.derniereVisite ? new Date(client.derniereVisite).toLocaleDateString() : 'Jamais'}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="app.editClient(${client.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="app.deleteClient(${client.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    // Utility Methods
    saveToStorage(key, data) {
        localStorage.setItem(key, JSON.stringify(data));
    }

    showSuccessMessage(message) {
        // Create and show success alert
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
        alert.style.top = '90px';
        alert.style.right = '20px';
        alert.style.zIndex = '9999';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alert);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 3000);
    }

    initializeData() {
        // Initialize with sample data if empty
        if (this.clients.length === 0) {
            this.clients = [
                {
                    id: 1,
                    nom: 'Dupont',
                    prenom: 'Marie',
                    email: 'marie.dupont@email.com',
                    telephone: '06 12 34 56 78',
                    adresse: '123 Rue de la Paix',
                    codePostal: '75001',
                    ville: 'Paris',
                    origine: 'internet',
                    notes: 'Cliente fidèle',
                    dateCreation: new Date().toISOString(),
                    derniereVisite: '2024-01-15'
                }
            ];
            this.saveToStorage('clients', this.clients);
        }

        if (this.reservations.length === 0) {
            this.reservations = [
                {
                    id: 1,
                    clientId: 1,
                    chambre: 'Lavande',
                    dateArrivee: '2024-01-20',
                    dateDepart: '2024-01-23',
                    nuitees: 3,
                    tarif: 80,
                    montantTotal: 240,
                    statut: 'confirmee',
                    arrhes: 50
                }
            ];
            this.saveToStorage('reservations', this.reservations);
        }
    }
}
document.addEventListener('DOMContentLoaded', () => {
    const calendarContainer = document.getElementById('calendar-container');
    const btnMonth = document.getElementById('view-month');
    const btnWeek = document.getElementById('view-week');
    const currentPeriod = document.getElementById('current-period');
    const prevBtn = document.getElementById('prev-period');
    const nextBtn = document.getElementById('next-period');

    let currentView = 'month'; // par défaut
    let currentDate = new Date();

    // Helpers
    function formatDateFR(date) {
        return date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        });
    }

    function setActiveButton(view) {
        if (view === 'month') {
            btnMonth.classList.add('active');
            btnWeek.classList.remove('active');
        } else {
            btnWeek.classList.add('active');
            btnMonth.classList.remove('active');
        }
    }

   
    // Vue Semaine
    function renderWeekView(date = new Date()) {
        calendarContainer.innerHTML = '';

        const day = date.getDay();
        const diffToMonday = day === 0 ? -6 : 1 - day;
        const monday = new Date(date);
        monday.setDate(date.getDate() + diffToMonday);

        const weekdays = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

        currentPeriod.textContent = `Semaine du ${formatDateFR(monday)}`;

        const headerRow = document.createElement('div');
        headerRow.classList.add('row');

        weekdays.forEach((dayName, index) => {
            const dayDate = new Date(monday);
            dayDate.setDate(monday.getDate() + index);

            const col = document.createElement('div');
            col.className = 'col border text-center';
            col.innerHTML = `<strong>${dayName}</strong><br>${dayDate.getDate()}/${dayDate.getMonth() + 1}`;
            headerRow.appendChild(col);
        });

        calendarContainer.appendChild(headerRow);
    }

    // Navigation
    prevBtn.addEventListener('click', () => {
        if (currentView === 'month') {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderMonthView(currentDate);
        } else {
            currentDate.setDate(currentDate.getDate() - 7);
            renderWeekView(currentDate);
        }
    });

    nextBtn.addEventListener('click', () => {
        if (currentView === 'month') {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderMonthView(currentDate);
        } else {
            currentDate.setDate(currentDate.getDate() + 7);
            renderWeekView(currentDate);
        }
    });

    // Boutons de vue
    btnMonth.addEventListener('click', () => {
        currentView = 'month';
        setActiveButton('month');
        renderMonthView(currentDate);
    });

    btnWeek.addEventListener('click', () => {
        currentView = 'week';
        setActiveButton('week');
        renderWeekView(currentDate);
    });

    // Initialisation
    renderMonthView(currentDate); // ou renderWeekView si tu préfères par défaut
});

// Initialize the application
const app = new LaBoissiere();

// Make app globally available for onclick handlers
window.app = app;