<?php

namespace App\DataFixtures;

use App\Entity\Film;
use App\Entity\Realisateur;
use App\Entity\Reservation;
use App\Entity\Salle;
use App\Entity\Seance;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // ---------------------------------------------------------------
        // 1. Les réalisateurs : nom => nationalité
        // ---------------------------------------------------------------
        $listeRealisateurs = [
            'Bong Joon-ho'      => 'sud-coréenne',
            'Céline Sciamma'    => 'française',
            'Christopher Nolan' => 'britannique',
            'Denis Villeneuve'  => 'canadienne',
            'Hayao Miyazaki'    => 'japonaise',
            'Justine Triet'     => 'française',
            'Ladj Ly'           => 'française',
        ];

        $realisateurs = [];

        foreach ($listeRealisateurs as $nom => $nationalite) {
            $realisateur = new Realisateur();
            $realisateur->setNom($nom);
            $realisateur->setNationalite($nationalite);

            $manager->persist($realisateur);
            $realisateurs[$nom] = $realisateur;
        }

        // ---------------------------------------------------------------
        // 2. Les films : [titre, réalisateur, genre, année de sortie, durée en minutes]
        // ---------------------------------------------------------------
        $catalogue = [
            ['Anatomie d\'une chute',             'Justine Triet',     'Drame',           2023, 151],
            ['Dune',                              'Denis Villeneuve',  'Science-fiction', 2021, 155],
            ['Dune : Deuxième partie',            'Denis Villeneuve',  'Science-fiction', 2024, 166],
            ['Inception',                         'Christopher Nolan', 'Science-fiction', 2010, 148],
            ['Interstellar',                      'Christopher Nolan', 'Science-fiction', 2014, 169],
            ['Le Garçon et le Héron',             'Hayao Miyazaki',    'Animation',       2023, 124],
            ['Le Voyage de Chihiro',              'Hayao Miyazaki',    'Animation',       2001, 125],
            ['Les Misérables',                    'Ladj Ly',           'Drame',           2019, 104],
            ['Oppenheimer',                       'Christopher Nolan', 'Drame',           2023, 180],
            ['Parasite',                          'Bong Joon-ho',      'Thriller',        2019, 132],
            ['Portrait de la jeune fille en feu', 'Céline Sciamma',    'Drame',           2019, 122],
        ];

        $films = [];

        foreach ($catalogue as [$titre, $nomRealisateur, $genre, $annee, $duree]) {
            $film = new Film();
            $film->setTitre($titre);
            $film->setGenre($genre);
            $film->setAnneeSortie($annee);
            $film->setDuree($duree);
            $film->setSynopsis($faker->realText(200));
            $film->setAffiche('https://placehold.co/400x600?text=' . urlencode($titre));

            // addFilm() remplit aussi $film->realisateur
            $realisateurs[$nomRealisateur]->addFilm($film);

            $manager->persist($film);
            $films[] = $film;
        }

        // ---------------------------------------------------------------
        // 3. Les salles : nom => capacité
        // ---------------------------------------------------------------
        $listeSalles = [
            'Salle Bassin de la Villette' => 220,
            'Salle Buttes-Chaumont'       => 140,
            'Salle Canal de l\'Ourcq'     => 90,
            'Salle Jaurès'                => 40,
        ];

        $salles = [];

        foreach ($listeSalles as $nom => $capacite) {
            $salle = new Salle();
            $salle->setNom($nom);
            $salle->setCapacite($capacite);

            $manager->persist($salle);
            $salles[] = $salle;
        }

        // ---------------------------------------------------------------
        // 4. Les séances : les 7 prochains jours, 3 horaires par salle
        //    [heure, minute, prix]
        // ---------------------------------------------------------------
        $horaires = [
            [14, 0, 9.5],
            [17, 15, 12.5],
            [20, 30, 12.5],
        ];

        $seances = [];

        for ($jour = 0; $jour < 7; $jour++) {
            foreach ($salles as $salle) {
                foreach ($horaires as [$heure, $minute, $prix]) {
                    $dateHeure = (new \DateTimeImmutable('today'))
                        ->modify('+' . $jour . ' days')
                        ->setTime($heure, $minute);

                    $seance = new Seance();
                    $seance->setDateHeur($dateHeure);
                    $seance->setPrix($prix);
                    $seance->setVersion($faker->randomElement(['VF', 'VOST']));

                    // addSeance() remplit aussi $seance->film et $seance->salle
                    $faker->randomElement($films)->addSeance($seance);
                    $salle->addSeance($seance);

                    $manager->persist($seance);
                    $seances[] = $seance;
                }
            }
        }

        // 7 jours × 4 salles × 3 horaires = 84 séances

        // ---------------------------------------------------------------
        // 5. Les réservations : 150 tentatives au hasard,
        //    sans jamais dépasser la capacité de la salle
        // ---------------------------------------------------------------
        $placesReservees = array_fill(0, count($seances), 0); // séance n° => places déjà réservées

        for ($i = 0; $i < 150; $i++) {
            $numero = $faker->numberBetween(0, count($seances) - 1);
            $seance = $seances[$numero];
            $nbPlaces = $faker->numberBetween(1, 4);

            $placesRestantes = $seance->getSalle()->getCapacite() - $placesReservees[$numero];

            if ($nbPlaces > $placesRestantes) {
                continue; // séance pleine : on passe
            }

            $reservation = new Reservation();
            $reservation->setNomClient($faker->name());
            $reservation->setEmailClient($faker->safeEmail());
            $reservation->setNbPlace($nbPlaces);
            $reservation->setDateReservation(
                \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-7 days'))
            );

            // addReservation() remplit aussi $reservation->seance
            $seance->addReservation($reservation);

            $manager->persist($reservation);
            $placesReservees[$numero] += $nbPlaces;
        }

        $manager->flush();
    }
}