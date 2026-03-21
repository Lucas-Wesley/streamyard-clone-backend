# 🎬 Roadmap: Clone do StreamYard

## O que é o StreamYard?

Um **estúdio de live streaming no navegador** que permite:
- Transmitir ao vivo para YouTube, Twitch, Facebook, LinkedIn, etc.
- Convidar participantes via link (sem instalar nada)
- Adicionar overlays, banners, logos e fundos
- Compartilhar tela
- Múltiplos layouts (solo, side-by-side, grid)
- Gravar sessões
- Chat integrado das plataformas

---

## 🧱 Arquitetura Geral

```mermaid
graph TB
    subgraph "Frontend (Browser)"
        A[React/Next.js App]
        B[WebRTC Client]
        C[Canvas Compositor]
    end

    subgraph "Backend"
        D[API REST]
        E[WebSocket Server - Signaling]
        F[Media Server - SFU]
    end

    subgraph "Streaming Pipeline"
        G[FFmpeg - Composição/Transcoding]
        H[RTMP Output]
    end

    subgraph "Plataformas"
        I[YouTube Live]
        J[Twitch]
        K[Facebook Live]
    end

    subgraph "Infra"
        L[(PostgreSQL / MySQL)]
        M[(Redis)]
        N[Object Storage - S3]
    end

    A --> B
    A --> C
    B <--> E
    B <--> F
    F --> G
    G --> H
    H --> I
    H --> J
    H --> K
    D --> L
    D --> M
    G --> N
```

---

## 📚 Tecnologias para Aprender

### 1. WebRTC (⭐ Mais Importante)
| O quê | Por quê |
|-------|---------|
| **getUserMedia API** | Capturar câmera e microfone do navegador |
| **RTCPeerConnection** | Conexão P2P de áudio/vídeo entre participantes |
| **Signaling (WebSocket)** | Trocar ofertas SDP e candidatos ICE para estabelecer conexão |
| **STUN/TURN Servers** | Atravessar firewalls e NATs |

> [!TIP]
> Comece criando um **videochat simples 1:1** antes de qualquer coisa. Isso solidifica os conceitos de WebRTC.

**Recursos:**
- [WebRTC for the Curious](https://webrtcforthecurious.com/) (gratuito)
- [MDN WebRTC Guide](https://developer.mozilla.org/en-US/docs/Web/API/WebRTC_API)

---

### 2. Media Server (SFU)
Para mais de 2 participantes, P2P puro não escala. Você precisa de um **SFU (Selective Forwarding Unit)**.

| Opção | Linguagem | Observação |
|-------|-----------|------------|
| **[LiveKit](https://livekit.io/)** | Go | ⭐ Recomendado — open source, SDKs prontos, mais fácil de começar |
| **[mediasoup](https://mediasoup.org/)** | Node.js/C++ | Muito usado, mais controle, curva de aprendizado maior |
| **[Janus](https://janus.conf.meetecho.com/)** | C | Poderoso mas complexo |

> [!IMPORTANT]
> **LiveKit** é a escolha mais moderna e tem SDKs para React, Flutter, etc. Ideal para quem está começando. Ele já resolve STUN/TURN, rooms, e signaling.

---

### 3. Composição de Vídeo e Overlays

Duas abordagens possíveis:

| Abordagem | Como funciona | Prós | Contras |
|-----------|--------------|------|---------|
| **Canvas no Browser** | Usa `<canvas>` para desenhar streams + overlays | Mais simples, controle visual | Performance no cliente |
| **FFmpeg no Server** | Servidor compõe os streams com overlays via FFmpeg | Qualidade consistente | Mais complexo, mais infra |

> [!TIP]
> Para um MVP, comece com **composição no Canvas do browser**. Depois migre para server-side com FFmpeg.

---

### 4. Streaming para Plataformas (RTMP)
| Tecnologia | Uso |
|------------|-----|
| **RTMP** | Protocolo padrão para enviar stream para YouTube/Twitch/Facebook |
| **FFmpeg** | Transcodifica e envia o stream via RTMP |
| **node-media-server** | Servidor RTMP em Node.js (para receber e reencaminhar) |

O fluxo simplificado:
```
Canvas/MediaRecorder → WebSocket/HTTP → Servidor → FFmpeg → RTMP → YouTube/Twitch
```

---

### 5. Stack de Desenvolvimento

#### Frontend
| Tech | Uso |
|------|-----|
| **React** ou **Next.js** | Interface do estúdio |
| **TypeScript** | Tipagem para código mais seguro |
| **Canvas API** | Composição visual dos streams |
| **MediaRecorder API** | Gravar a sessão no browser |

#### Backend — Opções por Linguagem

> [!NOTE]
> O backend de um projeto como esse tem **duas responsabilidades distintas**:
> 1. **API de Gerenciamento** — CRUD de usuários, salas, gravações, billing, autenticação (qualquer framework serve).
> 2. **Serviço de Mídia** — Signaling WebSocket, controle do SFU, integração com FFmpeg (mais acoplado ao ecossistema de mídia).
>
> Você pode usar uma **única linguagem** para tudo ou **separar** em microserviços. Abaixo estão as opções.

##### 🐘 PHP / Laravel (⭐ Recomendado para quem já conhece)
| Tech | Uso |
|------|-----|
| **Laravel 11+** | API REST, autenticação, dashboard, gerenciamento de salas e gravações |
| **Laravel Reverb** | WebSocket server nativo do Laravel para signaling e eventos em tempo real |
| **Laravel Queues (Horizon)** | Filas de jobs para processamento de vídeo, notificações |
| **Laravel Broadcasting** | Eventos em tempo real integrados com Reverb/Pusher |
| **FFmpeg (via shell)** | Transcoding e push RTMP (executado como job em background) |

> [!TIP]
> Com **Laravel Reverb** você tem WebSocket nativo sem depender de serviços externos. Isso resolve o signaling WebRTC e o chat em tempo real diretamente no Laravel. Para o SFU, use LiveKit como serviço separado — ele tem SDKs para PHP.

##### 🟢 Node.js (Ecossistema nativo de mídia)
| Tech | Uso |
|------|-----|
| **Express / Fastify** | API REST |
| **Socket.io / ws** | Signaling WebSocket, chat em tempo real, controle do estúdio |
| **FFmpeg** | Transcoding e push RTMP |
| **Bull / BullMQ** | Filas de jobs (processamento de vídeo) |

##### 🐍 Python (Alternativa com bom ecossistema)
| Tech | Uso |
|------|-----|
| **FastAPI / Django** | API REST |
| **Django Channels / python-socketio** | WebSocket para signaling |
| **Celery** | Filas de jobs assíncronos |
| **FFmpeg (subprocess)** | Transcoding e push RTMP |

##### 🦀 Go (Alta performance)
| Tech | Uso |
|------|-----|
| **Gin / Fiber** | API REST de alta performance |
| **gorilla/websocket** | WebSocket nativo |
| **goroutines** | Processamento concorrente nativo |
| **LiveKit (Go nativo)** | Se usar LiveKit, Go é a linguagem nativa dele |

> [!IMPORTANT]
> **Recomendação para este projeto:** Use **Laravel** para toda a API de gerenciamento + signaling via Reverb. Para o SFU, use **LiveKit** como serviço separado (ele roda standalone independente da linguagem do backend). Isso aproveita seu conhecimento existente em PHP/Laravel e mantém a arquitetura limpa.

#### Banco de Dados & Cache
| Tech | Uso |
|------|-----|
| **PostgreSQL** ou **MySQL** | Dados de usuários, salas, gravações |
| **Redis** | Cache, sessões, pub/sub em tempo real, filas (Laravel Horizon) |

#### Infra & Deploy
| Tech | Uso |
|------|-----|
| **Docker** | Containerização |
| **Nginx** | Reverse proxy, SSL |
| **S3 / MinIO** | Armazenamento de gravações |
| **Coturn** | Servidor TURN para WebRTC |

---

## 🗺️ Roadmap em Fases

### Fase 1 — Fundamentos (2-4 semanas)
- [ ] Aprender WebRTC: criar um videochat 1:1 no browser
- [ ] Entender Signaling com WebSocket (Reverb no Laravel ou Socket.io no Node)
- [ ] Configurar STUN/TURN (pode usar servidores públicos no início)
- [ ] Criar interface básica com React/Next.js
- [ ] Configurar backend (Laravel com Reverb **ou** Node.js com Socket.io)

### Fase 2 — Sala com Múltiplos Participantes (3-4 semanas)
- [ ] Integrar LiveKit como SFU (funciona com qualquer backend)
- [ ] Criar sistema de "salas" (rooms) — API no backend escolhido
- [ ] Convite por link (guest join sem login)
- [ ] Exibir múltiplos vídeos na tela

### Fase 3 — Estúdio Visual (3-4 semanas)
- [ ] Implementar layouts (solo, side-by-side, grid)
- [ ] Composição com Canvas API
- [ ] Adicionar overlays: banners de texto, logos, fundos
- [ ] Compartilhamento de tela (getDisplayMedia)

### Fase 4 — Streaming para Plataformas (2-3 semanas)
- [ ] Configurar FFmpeg no servidor
- [ ] Receber stream do browser (MediaRecorder → WebSocket)
- [ ] Push RTMP para YouTube/Twitch
- [ ] Multistreaming (enviar para múltiplas plataformas simultaneamente)

### Fase 5 — Funcionalidades Extras (3-4 semanas)
- [ ] Gravação e armazenamento (S3/MinIO)
- [ ] Chat integrado (YouTube/Twitch chat API)
- [ ] Controles do host: mutar participantes, remover, etc.
- [ ] Autenticação e dashboard de gerenciamento

### Fase 6 — Produção e Polimento (2-4 semanas)
- [ ] Deploy com Docker + Docker Compose
- [ ] Configurar Nginx + SSL
- [ ] Otimização de performance e qualidade
- [ ] Testes de carga
- [ ] Monitoramento e logs

---

## ⏱️ Estimativa Total
**~15 a 23 semanas** (4-6 meses) trabalhando consistentemente, dependendo do seu nível atual.

---

## 🎯 Ordem de Estudo Sugerida

### Com Laravel (PHP)
```mermaid
graph LR
    A[1. PHP/Laravel Avançado] --> B[2. WebRTC Basics]
    B --> C[3. Laravel Reverb + Broadcasting]
    C --> D[4. React + Canvas API]
    D --> E[5. LiveKit + PHP SDK]
    E --> F[6. FFmpeg Basics]
    F --> G[7. RTMP + Streaming]
    G --> H[8. Docker + Deploy]
```

### Com Node.js
```mermaid
graph LR
    A[1. JavaScript/TypeScript Avançado] --> B[2. WebRTC Basics]
    B --> C[3. WebSocket / Socket.io]
    C --> D[4. React + Canvas API]
    D --> E[5. LiveKit ou mediasoup]
    E --> F[6. FFmpeg Basics]
    F --> G[7. RTMP + Streaming]
    G --> H[8. Docker + Deploy]
```

> [!NOTE]
> Você já tem experiência com **Laravel/PHP**. A recomendação é usar **Laravel + Reverb** para a API e signaling, e **LiveKit** (serviço separado) para o SFU. Isso permite aproveitar o que você já sabe sem abrir mão da qualidade na parte de mídia. A API de gerenciamento (usuários, salas, billing) fica 100% no Laravel. O LiveKit cuida do pesado de áudio/vídeo.

---

## 💡 Projetos Intermediários para Praticar

Antes de construir o clone completo, faça esses mini-projetos:

### Com Laravel
1. **Chat em tempo real** — Laravel Reverb + React (1-2 dias)
2. **Videochat 1:1** — WebRTC + signaling via Reverb (3-5 dias)
3. **Videochat em grupo** — LiveKit + React + API Laravel (3-5 dias)
4. **Canvas compositor** — Desenhar vídeos + texto em Canvas (2-3 dias)
5. **Stream para YouTube** — FFmpeg + RTMP via Laravel Queue (2-3 dias)

### Com Node.js
1. **Chat em tempo real** — WebSocket + React (1-2 dias)
2. **Videochat 1:1** — WebRTC puro + signaling server (3-5 dias)
3. **Videochat em grupo** — LiveKit + React (3-5 dias)
4. **Canvas compositor** — Desenhar vídeos + texto em Canvas (2-3 dias)
5. **Stream para YouTube** — FFmpeg + RTMP (2-3 dias)

---

## 🔗 Recursos Essenciais

| Recurso | Link |
|---------|------|
| WebRTC for the Curious | https://webrtcforthecurious.com/ |
| LiveKit Docs | https://docs.livekit.io/ |
| LiveKit PHP SDK | https://github.com/agence104/livekit-server-sdk-php |
| Laravel Reverb Docs | https://laravel.com/docs/reverb |
| Laravel Broadcasting | https://laravel.com/docs/broadcasting |
| mediasoup Docs | https://mediasoup.org/documentation/ |
| FFmpeg Wiki | https://trac.ffmpeg.org/wiki |
| Canvas API (MDN) | https://developer.mozilla.org/en-US/docs/Web/API/Canvas_API |
| RTMP Specification | https://rtmp.veriskope.com/docs/spec/ |
| StreamYard (referência) | https://streamyard.com/ |
