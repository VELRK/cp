-- Extra columns so admins can create custom email templates (not only edit system ones).
-- Live installs also add these columns from Nb_mail_model::ensure_template_columns().
ALTER TABLE `nb_mail_templates` ADD `label` VARCHAR(120) NOT NULL DEFAULT '' AFTER `template_key`;
ALTER TABLE `nb_mail_templates` ADD `event_key` VARCHAR(64) NOT NULL DEFAULT '' AFTER `label`;
ALTER TABLE `nb_mail_templates` ADD `audience` VARCHAR(32) NOT NULL DEFAULT '' AFTER `event_key`;
ALTER TABLE `nb_mail_templates` ADD `to_email` VARCHAR(190) NULL DEFAULT NULL AFTER `audience`;
ALTER TABLE `nb_mail_templates` ADD `is_system` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_enabled`;
