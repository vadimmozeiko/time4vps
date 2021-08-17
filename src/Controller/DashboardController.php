<?php

namespace App\Controller;

use App\Service\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    public function __construct(private Connection $connection)
    {
    }

    #[Route('/', name: 'loginForm', methods: ['GET'])]
    public function loginForm(): Response
    {
        return $this->render('dashboard/login.html.twig');
    }


    #[Route('/api/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): RedirectResponse
    {
        $this->connection->authorize($request);

        if ($this->connection->isAuthorized()) {
            return $this->redirectToRoute('servers');
        }

        $this->addFlash('error_message', 'Error: unauthorized');
        return $this->redirectToRoute('loginForm');
    }

    #[Route('/api/logout', name: 'logout', methods: ['GET'])]
    public function logout(): RedirectResponse|Response
    {
        if ($this->connection->isAuthorized()) {
            $this->connection->logout();
            return $this->redirectToRoute('loginForm');
        }
        $this->addFlash('error_message', 'Error: not logged in');
        return $this->redirectToRoute('loginForm');
    }

    #[Route('/api/dashboard', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        if ($this->connection->isAuthorized()) {
            return $this->render('dashboard/index.html.twig');
        }
        return $this->redirectToRoute('loginForm');
    }


    #[Route('/api/servers', name: 'servers', methods: ['GET'])]
    public function getServerList(): Response
    {
        if ($this->connection->isAuthorized()) {
            $data = $this->connection->getResources('api/server');
            return $this->render('dashboard/server/index.html.twig', ['data' => $data]);

        }
        $this->addFlash('error_message', 'Error: unauthorized');
        return $this->redirectToRoute('loginForm');
    }


    #[Route('/api/server/{id}', name: 'show', methods: ['GET'])]
    public function getServerDetails($id): Response
    {
        if ($this->connection->isAuthorized()) {
            $data = $this->connection->getResources("api/server/$id");
            return $this->render('dashboard/server/show.html.twig', ['data' => $data, 'id' => $id]);

        }
        $this->addFlash('error_message', 'Error: unauthorized');
        return $this->redirectToRoute('loginForm');
    }


    #[Route('/api/server/reinstall/{id}', name: 'reinstallFrom', methods: ['GET'])]
    public function reinstallForm($id): Response
    {
        if ($this->connection->isAuthorized()) {
            $data = $this->connection->getResources("api/server/$id/oses");
            return $this->render('dashboard/server/reinstall.html.twig', ['oses' => $data, 'id' => $id]);
        }
        $this->addFlash('error_message', 'Error: unauthorized');
        return $this->redirectToRoute('loginForm');
    }

    #[Route('/api/server/reinstall/{id}', name: 'reinstall', methods: ['POST'])]
    public function reinstall($id, Request $request): Response
    {
        $os = $request->get('os');
        if ($this->connection->isAuthorized()) {
            $this->connection->postTaskWithArgs("api/server/$id/reinstall", ['os' => $os]);
            $this->addFlash('success_message', 'OS reinstall task created successfully');
            return $this->redirectToRoute('servers');
        }
        $this->addFlash('error_message', 'Error: unauthorized');
        return $this->redirectToRoute('loginForm');
    }

    #[Route('/api/server/rename/{id}', name: 'renameFrom', methods: ['GET'])]
    public function renameForm($id): Response
    {
        if ($this->connection->isAuthorized()) {
            $data = $this->connection->getResources("api/server/$id");
            return $this->render('dashboard/server/rename.html.twig', ['data' => $data, 'id' => $id]);
        }
        $this->addFlash('error_message', 'Error: unauthorized');
        return $this->redirectToRoute('loginForm');
    }

    #[Route('/api/server/rename/{id}', name: 'rename', methods: ['POST'])]
    public function rename($id, Request $request): Response
    {
        $hostname = $request->get('name');
        if ($this->connection->isAuthorized()) {
            $this->connection->postTaskWithArgs("api/server/$id/reinstall", ['hostname' => $hostname]);
            $this->addFlash('success_message', 'Hostname changes successfully');
            return $this->redirectToRoute('servers');
        }
        $this->addFlash('error_message', 'Error: unauthorized');
        return $this->redirectToRoute('loginForm');
    }

    #[Route('/api/server/resetPass/{id}', name: 'resetPass', methods: ['GET'])]
    public function resetPass($id): Response
    {
        if ($this->connection->isAuthorized()) {
            $this->connection->postTask("api/server/$id/resetpassword");
            $this->addFlash('success_message', 'Password task was created successfully');
            return $this->redirectToRoute('servers');
        }
        $this->addFlash('error_message', 'Error: unauthorized');
        return $this->redirectToRoute('loginForm');
    }

    #[Route('/api/server/restart/{id}', name: 'restart', methods: ['GET'])]
    public function restart($id): Response
    {
        if ($this->connection->isAuthorized()) {
            $this->connection->postTask("api/server/$id/reboot");
            $this->addFlash('success_message', 'Server restart task created successfully');
            return $this->redirectToRoute('servers');
        }
        $this->addFlash('error_message', 'Error: unauthorized');
        return $this->redirectToRoute('loginForm');
    }


}