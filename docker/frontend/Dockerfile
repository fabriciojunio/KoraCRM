# Imagem do frontend.
#
# Em desenvolvimento roda o Vite com recarga. Em produção o resultado é um
# punhado de arquivo estático servido por Nginx: a imagem final não tem Node,
# npm nem código-fonte.
#
# O contexto de construção é a raiz do repositório, porque a configuração do
# Nginx mora em docker/frontend.

# --------------------------------------------------------- desenvolvimento
FROM node:20-alpine AS desenvolvimento

WORKDIR /app
COPY frontend/package*.json ./
RUN npm ci
COPY frontend/ .

EXPOSE 3000
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]

# ----------------------------------------------------------------- construção
FROM node:20-alpine AS construcao

WORKDIR /app

# O Vite e o TypeScript são dependências de desenvolvimento: instalar só as de
# produção aqui deixa a construção sem compilador.
COPY frontend/package*.json ./
RUN npm ci

COPY frontend/ .
RUN npm run build

# ------------------------------------------------------------------ produção
FROM nginx:1.27-alpine AS producao

COPY --from=construcao /app/dist /usr/share/nginx/html
COPY docker/frontend/nginx.conf /etc/nginx/conf.d/default.conf

RUN touch /var/run/nginx.pid \
    && chown -R nginx:nginx /var/run/nginx.pid /var/cache/nginx /usr/share/nginx/html

USER nginx
EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --retries=3 \
    CMD wget -q --spider http://localhost:8080/ || exit 1

CMD ["nginx", "-g", "daemon off;"]
