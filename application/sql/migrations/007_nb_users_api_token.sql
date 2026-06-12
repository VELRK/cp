-- Mobile / API Bearer tokens for nb_users (Dream Villa Makers app)
ALTER TABLE nb_users
  ADD COLUMN api_token VARCHAR(64) DEFAULT NULL,
  ADD COLUMN api_token_expires_at DATETIME DEFAULT NULL,
  ADD UNIQUE KEY uq_nb_users_api_token (api_token);
