-- ============================================
-- Database Schema for Neon PostgreSQL
-- ============================================

DROP TABLE IF EXISTS balance_transactions CASCADE;
DROP TABLE IF EXISTS dakhila_dags CASCADE;
DROP TABLE IF EXISTS dakhila_owners CASCADE;
DROP TABLE IF EXISTS dakhila CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- ============================================
-- Table: users
-- ============================================
CREATE TABLE users (
    id            BIGSERIAL PRIMARY KEY,
    name          VARCHAR(255) NOT NULL,
    email         VARCHAR(255) NOT NULL UNIQUE,
    password      VARCHAR(255) NOT NULL,
    is_admin      SMALLINT NOT NULL DEFAULT 0,
    balance       NUMERIC(16,4) NOT NULL DEFAULT 0.0000,
    created_at    TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- Table: balance_transactions
-- ============================================
CREATE TABLE balance_transactions (
    id            BIGSERIAL PRIMARY KEY,
    user_id       BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    amount        NUMERIC(16,4) NOT NULL,
    type          VARCHAR(20) NOT NULL CHECK (type IN ('credit', 'debit')),
    description   VARCHAR(500) NULL,
    created_at    TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_trans_user_id ON balance_transactions (user_id);
CREATE INDEX idx_trans_created_at ON balance_transactions (created_at);

-- ============================================
-- Table: dakhila
-- ============================================
CREATE TABLE dakhila (
    id                    BIGSERIAL PRIMARY KEY,
    user_id               BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    verify_id             VARCHAR(50) UNIQUE NULL,
    registry_no           VARCHAR(100) NOT NULL,
    challan_no            VARCHAR(100) NULL,
    office_name           VARCHAR(255) NOT NULL,
    upazila               VARCHAR(100) NOT NULL,
    district              VARCHAR(100) NOT NULL,
    holding_no            VARCHAR(100) NOT NULL,
    mouja_jl              VARCHAR(255) NOT NULL,
    khatian_no            VARCHAR(100) NOT NULL,
    payment_year_bn       VARCHAR(20) NOT NULL,
    payment_year_en       VARCHAR(30) NOT NULL,
    payment_day           SMALLINT NOT NULL,
    payment_month         SMALLINT NOT NULL,
    payment_year          SMALLINT NOT NULL,
    three_years_plus_due  NUMERIC(16,4) NOT NULL DEFAULT 0.0000,
    last_three_years_due  NUMERIC(16,4) NOT NULL DEFAULT 0.0000,
    due_interest          NUMERIC(16,4) NOT NULL DEFAULT 0.0000,
    current_demand        NUMERIC(16,4) NOT NULL DEFAULT 0.0000,
    total_demand          NUMERIC(16,4) NOT NULL DEFAULT 0.0000,
    total_collection      NUMERIC(16,4) NOT NULL DEFAULT 0.0000,
    total_due             NUMERIC(16,4) NOT NULL DEFAULT 0.0000,
    comments              TEXT NULL,
    total_in_words        VARCHAR(500) NOT NULL,
    issue_date            DATE NOT NULL,
    created_at            TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_dakhila_user_id ON dakhila (user_id);
CREATE INDEX idx_dakhila_registry_no ON dakhila (registry_no);
CREATE INDEX idx_dakhila_holding_no ON dakhila (holding_no);
CREATE INDEX idx_dakhila_khatian_no ON dakhila (khatian_no);
CREATE INDEX idx_dakhila_created_at ON dakhila (created_at);

-- ============================================
-- Table: dakhila_owners
-- ============================================
CREATE TABLE dakhila_owners (
    id            BIGSERIAL PRIMARY KEY,
    dakhila_id    BIGINT NOT NULL REFERENCES dakhila(id) ON DELETE CASCADE ON UPDATE CASCADE,
    name          VARCHAR(255) NOT NULL,
    share         NUMERIC(16,6) NOT NULL DEFAULT 0.000000,
    sort_order    SMALLINT NOT NULL DEFAULT 1
);

CREATE INDEX idx_owners_dakhila_id ON dakhila_owners (dakhila_id);

-- ============================================
-- Table: dakhila_dags
-- ============================================
CREATE TABLE dakhila_dags (
    id            BIGSERIAL PRIMARY KEY,
    dakhila_id    BIGINT NOT NULL REFERENCES dakhila(id) ON DELETE CASCADE ON UPDATE CASCADE,
    dag_no        VARCHAR(50) NOT NULL,
    type          VARCHAR(255) NOT NULL,
    amount        NUMERIC(16,6) NOT NULL DEFAULT 0.000000,
    sort_order    SMALLINT NOT NULL DEFAULT 1
);

CREATE INDEX idx_dags_dakhila_id ON dakhila_dags (dakhila_id);
