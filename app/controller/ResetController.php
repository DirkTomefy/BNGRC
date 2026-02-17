<?php

namespace app\controller;

use flight\Engine;

class ResetController
{
    private Engine $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    public function resetAll(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            $this->app->db()->runQuery('CALL reset_all()');
            $_SESSION['reset_success'] = 'Réinitialisation effectuée.';
        } catch (\Throwable $e) {
            $_SESSION['reset_error'] = 'Erreur: ' . $e->getMessage();
        }

        $this->app->redirect('/dashboard');
    }
}
