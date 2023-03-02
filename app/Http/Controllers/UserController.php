<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class UserController extends Controller
{


    private UserRepository $userRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserRepository $userRepository)
    {
        //
        $this->userRepository = $userRepository;

    }

    //
    public function list(){
        return $this->sendResponse($this->userRepository->list());
    }

    public function searchSession(Request $request){
        $username = $request->query("username");
        return $this->sendResponse($this->userRepository->searchSession($username));
    }

    public function listActiveUsers(Request $request){
        $month = $request->query("month");
        $year = $request->query("year", 2021);
        return $this->sendResponse($this->userRepository->listActiveUsers($month, $year));
    }

    public function getMostCommonSessionDurations(Request $request){
        $month = $request->query("month");
        $year = $request->query("year", 2021);
        return $this->sendResponse($this->userRepository->getMostCommonSessionDurations($month, $year));
    }
    public function getUsersLoggedConsecutively(){
        return $this->sendResponse($this->userRepository->getUsersLoggedConsecutively());
    }

}
