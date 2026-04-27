<template>
  <div class="page-layout">
    <Sidebar />
    <main class="main-content">

      <!-- Topbar -->
      <header class="topbar">
        <div class="topbar-left">
          <h2 class="page-title">Dashboard</h2>
          <p class="page-sub">Selamat datang kembali, Admin 👋</p>
        </div>
        <div class="topbar-right">
          <div class="topbar-date">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            {{ todayDate }}
          </div>
          <button class="btn-refresh" @click="refresh">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14" stroke-width="2">
              <polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/>
              <path d="M20.49 9A9 9 0 005.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 013.51 15"/>
            </svg>
            Refresh
          </button>
        </div>
      </header>

      <div class="content-body">

        <!-- Stats -->
        <div class="stats-grid">
          <div class="stat-card" v-for="s in stats" :key="s.label">
            <div class="stat-info">
              <p class="stat-label">{{ s.label }}</p>
              <p class="stat-value" :style="{ color: s.valueColor || '#1e1d4c' }">{{ s.value }}</p>
              <p class="stat-desc" v-if="s.desc">{{ s.desc }}</p>
            </div>
            <div class="stat-icon" :style="{ background: s.iconBg, color: s.iconColor }">
              <span v-html="s.icon"></span>
            </div>
          </div>
        </div>

        <!-- Reminder Table -->
        <div class="card">
          <div class="card-header">
            <div class="card-header-left">
              <div class="card-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                  <path d="M13.73 21a2 2 0 01-3.46 0"/>
                </svg>
              </div>
              <div>
                <h3 class="card-title">Reminder Servis</h3>
                <p class="card-subtitle">Kendaraan yang perlu perhatian segera</p>
              </div>
            </div>
            <span class="card-badge">{{ reminders.length }} item</span>
          </div>
          <div class="table-wrap">
            <table class="table">
              <thead>
                <tr>
                  <th>Kendaraan</th>
                  <th>Komponen</th>
                  <th>Kategori</th>
                  <th class="text-right">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="r in reminders"
                  :key="r.id"
                  @click="$router.push('/monitoring-kendaraan/' + r.monitoring_id)"
                  class="clickable"
                >
                  <td>
                    <div class="vehicle-cell">
                      <div class="vehicle-dot" :class="r.dotClass"></div>
                      <div>
                        <span class="fw-semibold">{{ r.nopol }}</span>
                        <div style="font-size: 0.7rem; color: #9ca3af;">{{ r.merk }} | {{ r.jenis }}</div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="fw-semibold" style="font-size: 0.82rem;">{{ r.nama_komponen }}</div>
                  </td>
                  <td>
                    <span class="component-chip">{{ r.kategori }}</span>
                  </td>
                  <td class="text-right">
                    <div style="font-size: 0.75rem; font-weight: 700; color: #dc2626; margin-bottom: 2px;">{{ r.health }}%</div>
                    <span class="badge" :class="r.badgeClass">{{ r.status }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </main>
  </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import Sidebar from '@/components/Sidebar.vue'

export default {
  name: 'DashboardView',
  components: { Sidebar },
  data() {
    return {
    }
  },
  computed: {
    ...mapState('armada', ['loading']),
    ...mapState('monitoring', ['reminders']),
    ...mapGetters('armada', ['armadaList']),

    todayDate() {
      return new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
    },

    stats() {
      return [
        {
          label: 'Total Kendaraan',
          value: this.armadaList.length,
          desc: 'Terdaftar aktif',
          icon: '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17h14v-5H5v5zM5 12l2-7h10l2 7"/><circle cx="7.5" cy="17" r="2"/><circle cx="16.5" cy="17" r="2"/></svg>',
          iconBg: '#ede9fe', iconColor: '#6d28d9'
        },
        {
          label: 'Reminder Servis',
          value: this.reminders.length,
          desc: 'Perlu tindakan segera',
          valueColor: '#dc2626',
          icon: '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>',
          iconBg: '#fee2e2', iconColor: '#dc2626'
        }
      ]
    }
  },
  mounted() {
    this.fetchData()
  },
  methods: {
    ...mapActions('armada', ['fetchArmada']),
    ...mapActions('monitoring', ['fetchReminders']),

    async fetchData() {
      await Promise.all([
        this.fetchArmada(),
        this.fetchReminders()
      ])
    },

    refresh() {
      this.fetchData()
    }
  }
}
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Poppins', sans-serif; }
</style>

<style scoped>
/* ===== LAYOUT ===== */
.page-layout {
  display: flex;
  min-height: 100vh;
  font-family: 'Poppins', sans-serif;
}

.main-content {
  flex: 1;
  background: #f0f0f8;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

/* ===== TOPBAR ===== */
.topbar {
  background: #fff;
  border-bottom: 1px solid #e8e8f0;
  min-height: 80px;
  box-sizing: border-box;
  padding: 0 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: sticky;
  top: 0;
  z-index: 10;
}

.page-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e1d4c;
  line-height: 1.2;
}

.page-sub {
  font-size: 0.78rem;
  color: #9ca3af;
  margin-top: 2px;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.topbar-date {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.78rem;
  color: #6b7280;
  background: #f5f5fb;
  padding: 0.4rem 0.85rem;
  border-radius: 8px;
  border: 1px solid #e8e8f0;
}

.btn-refresh {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 0.45rem 1rem;
  background: #fff;
  border: 1px solid #e8e8f0;
  border-radius: 8px;
  font-family: 'Poppins', sans-serif;
  font-size: 0.8rem;
  font-weight: 500;
  color: #374151;
  cursor: pointer;
  transition: all 0.15s;
}

.btn-refresh:hover {
  background: #f5f5fb;
  border-color: #3E3D90;
  color: #3E3D90;
}

/* ===== CONTENT BODY ===== */
.content-body {
  flex: 1;
  padding: 1.75rem 2rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* ===== STATS GRID ===== */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.stat-card {
  background: #fff;
  border-radius: 14px;
  padding: 1.4rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border: 1px solid #e8e8f0;
  transition: transform 0.15s, box-shadow 0.15s;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(62, 61, 144, 0.1);
}

.stat-info { display: flex; flex-direction: column; gap: 3px; }

.stat-label {
  font-size: 0.78rem;
  font-weight: 500;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-value {
  font-size: 2.2rem;
  font-weight: 700;
  line-height: 1.1;
  color: #1e1d4c;
}

.stat-desc {
  font-size: 0.72rem;
  color: #b0b0c8;
}

.stat-icon {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

/* ===== CARD ===== */
.card {
  background: #fff;
  border-radius: 14px;
  border: 1px solid #e8e8f0;
  overflow: hidden;
}

.card-header {
  padding: 1.1rem 1.5rem;
  border-bottom: 1px solid #f0f0f8;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.card-header-left {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.card-icon {
  width: 36px;
  height: 36px;
  background: #ede9fe;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6d28d9;
  flex-shrink: 0;
}

.card-title {
  font-size: 0.95rem;
  font-weight: 600;
  color: #1e1d4c;
}

.card-subtitle {
  font-size: 0.72rem;
  color: #9ca3af;
  margin-top: 1px;
}

.card-badge {
  padding: 0.22rem 0.7rem;
  background: #f0f0f8;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 600;
  color: #6b7280;
}

/* ===== TABLE ===== */
.table-wrap { overflow-x: auto; }

.table {
  width: 100%;
  border-collapse: collapse;
}

.table thead th {
  padding: 0.7rem 1.5rem;
  font-size: 0.7rem;
  font-weight: 600;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  background: #fafafa;
  text-align: left;
  border-bottom: 1px solid #f0f0f8;
}

.table thead th.text-right { text-align: right; }

.table tbody td {
  padding: 0.95rem 1.5rem;
  font-size: 0.85rem;
  color: #374151;
  border-bottom: 1px solid #f5f5fb;
  vertical-align: middle;
}

.table tbody tr:last-child td { border-bottom: none; }

.clickable { cursor: pointer; }
.clickable:hover td { background: #faf9ff; }

/* ===== VEHICLE CELL ===== */
.vehicle-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}

.vehicle-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}

.dot-red { background: #ef4444; box-shadow: 0 0 6px rgba(239, 68, 68, 0.5); }
.dot-yellow { background: #f59e0b; box-shadow: 0 0 6px rgba(245, 158, 11, 0.5); }
.dot-orange { background: #f97316; box-shadow: 0 0 6px rgba(249, 115, 22, 0.5); }

.fw-semibold { font-weight: 600; color: #1e1d4c; }

/* ===== COMPONENT CHIP ===== */
.component-chip {
  display: inline-flex;
  padding: 0.22rem 0.7rem;
  background: #f5f5fb;
  border-radius: 6px;
  font-size: 0.78rem;
  font-weight: 500;
  color: #4b5563;
  border: 1px solid #e8e8f0;
}

/* ===== BADGE ===== */
.badge {
  display: inline-flex;
  padding: 0.28rem 0.8rem;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 600;
  letter-spacing: 0.2px;
}

.badge-red    { background: #fee2e2; color: #991b1b; }
.badge-yellow { background: #fef3c7; color: #92400e; }
.badge-orange { background: #ffedd5; color: #9a3412; }

.text-right { text-align: right; }

/* ===== RESPONSIVE ===== */
@media (max-width: 900px) {
  .stats-grid { grid-template-columns: 1fr; }
  .content-body { padding: 1.25rem; }
  .topbar { padding: 1rem 1.25rem; }
  .topbar-date { display: none; }
}

@media (max-width: 768px) {
  .topbar {
    padding: 0.85rem 1rem 0.85rem 4rem; /* ruang untuk hamburger */
    flex-wrap: wrap;
    gap: 0.5rem;
  }
  .page-title { font-size: 1rem; }
  .content-body { padding: 1rem; gap: 1rem; }
  .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
  .table thead th:nth-child(2),
  .table tbody td:nth-child(2),
  .table thead th:nth-child(3),
  .table tbody td:nth-child(3) { display: none; }
  .table thead th,
  .table tbody td { padding: 0.75rem 1rem; }
}

@media (max-width: 480px) {
  .stats-grid { grid-template-columns: 1fr; }
  .topbar-right { gap: 0.4rem; }
  .btn-refresh { padding: 0.4rem 0.7rem; font-size: 0.75rem; }
  .table tbody td { padding: 0.7rem 0.75rem; font-size: 0.8rem; }
  .badge { font-size: 0.65rem; padding: 0.22rem 0.6rem; }
  .card-header { padding: 0.85rem 1rem; }
}
</style>