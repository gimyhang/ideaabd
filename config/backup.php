<?php

/**
 * Backup hub (/admin/backup). Every value can be changed in .env.
 */
return [
    // Where the backup files are kept (default: storage/app/backups, which is not reachable from the web).
    'dir' => env('BACKUP_DIR'),

    // Disk space the hosting plan gives you, in MB. 0 = use the real size of the disk the backups are stored on.
    'quota_mb' => (int) env('BACKUP_QUOTA_MB', 0),

    // Backups may use this share of that space; older ones are deleted when it is crossed.
    'max_backup_pct' => (int) env('BACKUP_MAX_PCT', 40),

    // Storage bar turns amber / red at this share used by backups.
    'warn_pct'     => 25,
    'critical_pct' => 40,

    // How many regular backups to keep (the admin can change it in the settings, up to max_retention).
    'default_retention' => (int) env('BACKUP_RETENTION', 3),
    'max_retention'     => 30,

    // Safety snapshots (taken before every restore) and masked developer dumps keep only the newest few.
    'keep_safety'     => 2,
    'keep_anonymized' => 2,

    // Folders packed into a "database + media" backup (name inside the ZIP => folder on the server).
    // On restore, files under "media/" go back to the "media" folder.
    'media' => [
        'media'          => storage_path('app/public'),
        'public_uploads' => public_path('uploads'),
    ],

    // Biggest file the upload box accepts, in MB (the PHP limits upload_max_filesize / post_max_size still apply).
    'upload_max_mb' => (int) env('BACKUP_UPLOAD_MAX_MB', 200),

    // A backup bigger than this is not attached to an email, only announced.
    'mail_attach_max_mb' => (int) env('BACKUP_MAIL_MAX_MB', 20),

    // Files that are never copied back from a media archive.
    'blocked_media_extensions' => ['php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'php8', 'pht', 'shtml', 'cgi', 'pl', 'sh', 'bat', 'exe', 'htaccess', 'htpasswd'],

    // Weekly backups run on this weekday (0 = Sunday … 5 = Friday).
    'weekly_day' => 5,
];
