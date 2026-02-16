<?php

namespace app\controller;

use app\model\Besoin;
use app\model\Don;
use app\model\Element;
use flight\Engine;

class DonController
{
    private Engine $app;
    private Element $elementModel;
    private Don $donModel;
    private Besoin $besoinModel;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->elementModel = new Element($app->db());
        $this->donModel = new Don($app->db());
        $this->besoinModel = new Besoin($app->db());
    }

    /**
     * Affiche le formulaire de saisie + traite l'ajout au panier temporaire (session)
     * L'utilisateur saisit uniquement l'élément, quantité, date et description.
     * Pas de sélection de ville : la répartition se fait en FIFO lors de la distribution.
     */
    public function saisie(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $success = '';
        $error = '';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            try {
                $idElement = (int)($_POST['element'] ?? 0);
                $quantite = (int)($_POST['quantite'] ?? 0);
                $date = $_POST['date'] ?? date('Y-m-d');
                $description = trim($_POST['description'] ?? '');

                if (empty($idElement) || empty($quantite) || empty($date)) {
                    throw new \Exception('Tous les champs obligatoires doivent être remplis');
                }

                if ($quantite <= 0) {
                    throw new \Exception('La quantité doit être supérieure à 0');
                }

                // Récupérer les libellés pour l'affichage dans le tableau temporaire
                $elementInfo = $this->elementModel->getById($idElement);

                if (!$elementInfo) {
                    throw new \Exception('Élément invalide');
                }

                // Ajouter au panier en session
                if (!isset($_SESSION['panier_dons'])) {
                    $_SESSION['panier_dons'] = [];
                }

                $_SESSION['panier_dons'][] = [
                    'id_element'        => $idElement,
                    'element_libele'    => $elementInfo['libele'],
                    'element_pu'        => (float)$elementInfo['pu'],
                    'type_besoin'       => $elementInfo['type_besoin_libele'] ?? '',
                    'quantite'          => $quantite,
                    'date'              => $date,
                    'description'       => $description,
                ];

                $success = 'Don ajouté au panier ! Vous pouvez continuer à ajouter ou distribuer.';
                $_POST = [];
            } catch (\Exception $e) {
                $error = 'Erreur: ' . $e->getMessage();
            }
        }

        $elements = $this->elementModel->getAll();
        $panierDons = $_SESSION['panier_dons'] ?? [];

        $this->app->render('don/saisie', [
            'elements'      => $elements,
            'success'       => $success,
            'error'         => $error,
            'form'          => $_POST,
            'panierDons'    => $panierDons,
        ]);
    }

    /**
     * Supprime un don du panier temporaire
     */
    public function supprimerDuPanier(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $index = (int)($_POST['index'] ?? -1);

        if (isset($_SESSION['panier_dons'][$index])) {
            array_splice($_SESSION['panier_dons'], $index, 1);
        }

        $this->app->redirect('/don/saisie');
    }

    /**
     * Vide le panier temporaire
     */
    public function viderPanier(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['panier_dons'] = [];
        $this->app->redirect('/don/saisie');
    }

    /**
     * Distribue tous les dons du panier en FIFO :
     * Pour chaque don du panier, on cherche le besoin le plus ancien (par date ASC, id ASC)
     * qui demande le même élément et qui n'est pas encore satisfait.
     * Le don est inséré dans bn_don avec l'idVille du besoin trouvé.
     * Si aucun besoin n'existe pour cet élément, le don est ignoré (ou signalé).
     */
    public function distribuer(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $panierDons = $_SESSION['panier_dons'] ?? [];

        if (empty($panierDons)) {
            $_SESSION['don_error'] = 'Le panier est vide, rien à distribuer.';
            $this->app->redirect('/don/saisie');
            return;
        }

        try {
            $nbDistribues = 0;
            $nonDistribues = [];

            foreach ($panierDons as $don) {
                $quantiteRestante = (int)$don['quantite'];
                $idElement = (int)$don['id_element'];

                // Récupérer les besoins non satisfaits pour cet élément, triés FIFO (date ASC, id ASC)
                $besoins = $this->getBesoinsNonSatisfaits($idElement);

                if (empty($besoins)) {
                    $nonDistribues[] = $don['element_libele'] . ' (qté: ' . $don['quantite'] . ')';
                    continue;
                }

                // Distribuer en FIFO sur les besoins les plus anciens
                foreach ($besoins as $besoin) {
                    if ($quantiteRestante <= 0) {
                        break;
                    }

                    $quantiteBesoin = (int)$besoin['quantite'];
                    $quantiteADonner = min($quantiteRestante, $quantiteBesoin);

                    // Insérer le don dans bn_don avec la ville du besoin
                    $this->donModel->insertDon(
                        (int)$besoin['idVille'],
                        $idElement,
                        $quantiteADonner,
                        $don['date'],
                        $don['description']
                    );

                    $quantiteRestante -= $quantiteADonner;
                    $nbDistribues++;
                }

                // S'il reste de la quantité non distribuée, on le signale
                if ($quantiteRestante > 0) {
                    $nonDistribues[] = $don['element_libele'] . ' (reste: ' . $quantiteRestante . ')';
                }
            }

            // Vider le panier après distribution
            $_SESSION['panier_dons'] = [];

            $message = $nbDistribues . ' don(s) distribué(s) avec succès aux villes par FIFO !';
            if (!empty($nonDistribues)) {
                $message .= ' | Non distribués (aucun besoin trouvé) : ' . implode(', ', $nonDistribues);
            }
            $_SESSION['don_success'] = $message;
        } catch (\Exception $e) {
            $_SESSION['don_error'] = 'Erreur lors de la distribution : ' . $e->getMessage();
        }

        $this->app->redirect('/don/saisie');
    }

    /**
     * Récupère les besoins non satisfaits pour un élément donné, triés FIFO (date ASC, id ASC)
     */
    private function getBesoinsNonSatisfaits(int $idElement): array
    {
        return $this->app->db()->fetchAll("
            SELECT b.id, b.idelement, b.quantite, b.idVille, b.date,
                   v.libele as ville_libele
            FROM bn_besoin b
            LEFT JOIN bn_ville v ON b.idVille = v.id
            WHERE b.idelement = ?
            ORDER BY b.date ASC, b.id ASC
        ", [$idElement]);
    }
}
