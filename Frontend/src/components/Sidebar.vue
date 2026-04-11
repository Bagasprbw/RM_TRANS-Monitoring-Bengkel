<template>
  <div class="sidebar-wrapper">
    <!-- Mobile Toggle Button -->
    <button class="hamburger-btn" @click="isOpen = !isOpen" :class="{ open: isOpen }" aria-label="Toggle Menu">
      <span></span>
      <span></span>
      <span></span>
    </button>

    <!-- Mobile Overlay -->
    <div class="sidebar-overlay" v-if="isOpen" @click="isOpen = false"></div>

    <!-- Sidebar -->
    <aside class="sidebar" :class="{ 'sidebar-open': isOpen }">
      <!-- Close button (mobile) -->
      <button class="sidebar-close-btn" @click="isOpen = false" aria-label="Tutup menu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18" />
          <line x1="6" y1="6" x2="18" y2="18" />
        </svg>
      </button>

      <!-- Logo -->
      <div class="sidebar-logo">
        <img src="/logo.png" alt="RM Trans" class="sidebar-logo-img" />
      </div>

      <!-- Navigation -->
      <nav class="sidebar-nav">
        <p class="nav-section-label">Menu Utama</p>
        <router-link v-for="item in menuItems" :key="item.path" :to="item.path" class="nav-item"
          :class="{ active: $route.path === item.path || $route.path.startsWith(item.path + '/') }"
          @click.native="isOpen = false">
          <span class="nav-icon" v-html="item.icon"></span>
          <span class="nav-label">{{ item.label }}</span>
          <span v-if="item.badge" class="nav-badge"></span>
        </router-link>
      </nav>

      <!-- User + Logout -->
      <div class="sidebar-footer">
        <div class="user-info" v-if="user" @click="$router.push('/profile')" title="Buka Profil Anda">
          <div class="user-avatar">{{ userInitial }}</div>
          <div class="user-details">
            <span class="user-name">{{ user.name }}</span>
            <span class="user-role">{{ user.role }}</span>
          </div>
        </div>
        <button class="logout-btn" @click="handleLogout">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="16" height="16" stroke-width="2">
            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" />
            <polyline points="16,17 21,12 16,7" />
            <line x1="21" y1="12" x2="9" y2="12" />
          </svg>
          Keluar
        </button>
      </div>
    </aside>
  </div>
</template>

<script>
import Swal from 'sweetalert2'

export default {
  name: 'Sidebar',
  data() {
    return {
      isOpen: false,
      menuItems: [
        {
          path: '/dashboard',
          label: 'Dashboard',
          icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>'
        },
        {
          path: '/kendaraan',
          label: 'Daftar Kendaraan',
          icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17h14v-5H5v5zM5 12l2-7h10l2 7"/><circle cx="7.5" cy="17" r="2"/><circle cx="16.5" cy="17" r="2"/></svg>'
        },
        {
          path: '/monitoring-kendaraan',
          label: 'Monitoring',
          icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
          badge: true
        }
      ]
    }
  },
  computed: {
    user() {
      return this.$store.state.auth.user
    },
    userInitial() {
      if (!this.user || !this.user.name) return '?'
      return this.user.name.charAt(0).toUpperCase()
    }
  },
  methods: {
    async handleLogout() {
      const result = await Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Anda akan keluar dari aplikasi!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3E3D90',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Keluar!',
        cancelButtonText: 'Batal'
      })

      if (result.isConfirmed) {
        await this.$store.dispatch('auth/logout')
        this.isOpen = false
        
        Swal.fire({
          title: 'Berhasil!',
          text: 'Anda telah keluar.',
          icon: 'success',
          timer: 1500,
          showConfirmButton: false
        })

        this.$router.push('/login')
      }
    }
  }

}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

/* ===== WRAPPER ===== */
.sidebar-wrapper {
  position: relative;
  flex-shrink: 0;
}

/* ===== HAMBURGER ===== */
.hamburger-btn {
  display: none;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 5px;
  width: 40px;
  height: 40px;
  background: #fff;
  border: 1.5px solid #e8e8f0;
  border-radius: 10px;
  cursor: pointer;
  position: fixed;
  top: 14px;
  left: 14px;
  z-index: 200;
  transition: all 0.2s;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.hamburger-btn span {
  display: block;
  width: 18px;
  height: 2px;
  background: #374151;
  border-radius: 2px;
  transition: all 0.25s ease;
  transform-origin: center;
}

.hamburger-btn.open span:nth-child(1) {
  transform: translateY(7px) rotate(45deg);
}

.hamburger-btn.open span:nth-child(2) {
  opacity: 0;
  transform: scaleX(0);
}

.hamburger-btn.open span:nth-child(3) {
  transform: translateY(-7px) rotate(-45deg);
}

/* ===== OVERLAY ===== */
.sidebar-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(15, 15, 40, 0.45);
  z-index: 90;
  backdrop-filter: blur(2px);
}

/* ===== CLOSE BUTTON (mobile only) ===== */
.sidebar-close-btn {
  display: none;
  position: absolute;
  top: 12px;
  right: 12px;
  background: #f5f5fb;
  border: none;
  border-radius: 8px;
  color: #9ca3af;
  cursor: pointer;
  padding: 6px;
  align-items: center;
  transition: all 0.15s;
}

.sidebar-close-btn:hover {
  background: #f0f0f0;
  color: #374151;
}

.sidebar {
  position: sticky;
  top: 0;
  height: 100vh;
  overflow-y: auto;
  width: 230px;
  background: #ffffff;
  border-right: 1px solid #e8e8f0;
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  font-family: 'Poppins', sans-serif;
}

/* ===== LOGO ===== */
.sidebar-logo {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 80px;
  box-sizing: border-box;
  padding: 0 1.25rem;
  border-bottom: 1px solid #e8e8f0;
}

.sidebar-logo-img {
  max-width: 150px;
  height: auto;
  object-fit: contain;
}

/* ===== NAV ===== */
.sidebar-nav {
  flex: 1;
  padding: 1.2rem 0.85rem;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.nav-section-label {
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 1.2px;
  text-transform: uppercase;
  color: #b0b0c8;
  padding: 0 0.75rem;
  margin-bottom: 0.5rem;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.7rem 0.85rem;
  border-radius: 10px;
  color: #6b7280;
  text-decoration: none;
  font-size: 0.85rem;
  font-weight: 500;
  transition: all 0.18s ease;
  position: relative;
}

.nav-item:hover {
  background: #f3f4ff;
  color: #3E3D90;
}

.nav-item.active {
  background: #f0f0fb;
  color: #3E3D90;
  font-weight: 600;
  border-left: 3px solid #3E3D90;
}

.nav-item.active .nav-icon {
  color: #3E3D90;
}

.nav-icon {
  display: flex;
  align-items: center;
  flex-shrink: 0;
}

.nav-label {
  flex: 1;
}

.nav-badge {
  width: 7px;
  height: 7px;
  background: #ef4444;
  border-radius: 50%;
  box-shadow: 0 0 6px rgba(248, 113, 113, 0.7);
}

/* ===== FOOTER ===== */
.sidebar-footer {
  padding: 0.85rem;
  border-top: 1px solid #e8e8f0;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  padding: 0.6rem 0.75rem;
  margin-bottom: 6px;
  border-radius: 10px;
  background: #f5f5fb;
  cursor: pointer;
  transition: all 0.15s;
}

.user-info:hover {
  background: #ede9fe;
}

.user-avatar {
  width: 34px;
  height: 34px;
  background: #3E3D90;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 13px;
  font-weight: 700;
  flex-shrink: 0;
}

.user-details {
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.user-name {
  font-size: 0.78rem;
  font-weight: 600;
  color: #1e1d4c;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-role {
  font-size: 0.68rem;
  color: #9ca3af;
}

.logout-btn {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  width: 100%;
  padding: 0.65rem 0.85rem;
  border-radius: 10px;
  background: none;
  border: none;
  color: #ef4444;
  font-family: 'Poppins', sans-serif;
  font-size: 0.83rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
}

.logout-btn:hover {
  background: #fff0f0;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
  .hamburger-btn {
    display: flex;
  }

  .sidebar-overlay {
    display: block;
  }

  .sidebar-close-btn {
    display: flex;
  }

  .sidebar {
    position: fixed;
    left: -260px;
    top: 0;
    height: 100vh;
    width: 260px;
    z-index: 100;
    transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 4px 0 24px rgba(0, 0, 0, 0.12);
  }

  .sidebar.sidebar-open {
    left: 0;
  }
}
</style>