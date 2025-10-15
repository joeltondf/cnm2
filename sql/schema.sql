CREATE TABLE estados (
    codigo INT PRIMARY KEY,
    uf CHAR(2) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    regiao VARCHAR(50) NOT NULL,
    UNIQUE KEY uq_estados_uf (uf)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE municipios (
    codigo_ibge VARCHAR(10) PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    uf CHAR(2) NOT NULL,
    capital TINYINT(1) DEFAULT 0,
    latitude DECIMAL(10, 7) NULL,
    longitude DECIMAL(10, 7) NULL,
    siafi_id VARCHAR(20) NULL,
    ddd VARCHAR(5) NULL,
    fuso_horario VARCHAR(40) NULL,
    CONSTRAINT fk_municipios_estados FOREIGN KEY (uf) REFERENCES estados (uf)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
