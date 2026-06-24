# ============================================================
# STAGE 1: Build Vue 2 (Vite) Frontend
# ============================================================
FROM node:20-alpine AS builder

WORKDIR /app

# Install dependencies
COPY package.json package-lock.json ./
RUN npm ci

# Salin semua source code
COPY . .

# Build untuk production
# VITE_API_BASE_URL akan di-inject saat runtime lewat Nginx config
RUN npm run build

# ============================================================
# STAGE 2: Serve dengan Nginx
# ============================================================
FROM nginx:1.25-alpine

# Hapus default nginx config
RUN rm /etc/nginx/conf.d/default.conf

# Salin hasil build dari stage 1
COPY --from=builder /app/dist /usr/share/nginx/html

# Salin konfigurasi Nginx custom
COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
