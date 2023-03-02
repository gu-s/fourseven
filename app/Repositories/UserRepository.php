<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class UserRepository{

    public function list(){
        return  DB::select('select * from sys_users');
    }

    public function searchSession($username){
        return  DB::select("
        select 
        DATE_FORMAT(FROM_UNIXTIME(s.login_tstamp), '%d-%m-%Y  %H:%i') from_date,
        DATE_FORMAT(FROM_UNIXTIME(s.logout_tstamp), '%d-%m-%Y  %H:%i') to_date,
        s.ip_address
        from sys_users u
        inner join sys_user_sessions s on u.id = s.user_id
        where u.username = :username
        order by s.login_tstamp desc
        ", 
        ['username' => $username]
        );
    }

    public function listActiveUsers($month = 9, $year= 2021){
        return  DB::select("
            select 
            u.id,
            u.username,
            concat(upper(left(u.first_name, 1)),'.', u.surname ) n_surname
            from (
                select distinct(u_per_week.user_id) user_id
                from (
                    select 
                    user_id,
                    s_weekly.week_number 
                    -- ,count(s_weekly.week_number) c_in_week
                    from (
                        select 
                        s.user_id,
                        WEEKOFYEAR(FROM_UNIXTIME(s.login_tstamp)) week_number
                        from sys_user_sessions s
                        where MONTH(FROM_UNIXTIME(s.login_tstamp)) = :month  
                        and YEAR(FROM_UNIXTIME(s.login_tstamp)) = :year
                    ) s_weekly
                    group by s_weekly.user_id,s_weekly.week_number
                    having count(s_weekly.week_number)  >= 3
                    order by user_id
                ) u_per_week
                
            ) u_in_month 
            inner join sys_users u on u.id =  u_in_month.user_id
        ", 
        ['month' => $month, 'year' => $year]);
    }

    public function getMostCommonSessionDurations($month = 9, $year= 2021){
        return  DB::select("
        select  (s_durations.duration_by_30 * 30) duration_by_30,
        count(s_durations.id) sessions
        from (
            select 
            s.id,
            (TIMESTAMPDIFF(MINUTE,FROM_UNIXTIME(s.login_tstamp),FROM_UNIXTIME(s.logout_tstamp))) DIV 30 duration_by_30
            from sys_user_sessions s
            where MONTH(FROM_UNIXTIME(s.logout_tstamp)) = :month    
            and YEAR(FROM_UNIXTIME(s.logout_tstamp)) = :year
        ) s_durations
        group by s_durations.duration_by_30
        order by s_durations.duration_by_30 asc
        ", 
        ['month' => $month, 'year' => $year]);
    }

    
    public function getUsersLoggedConsecutively(){
        //last session active to get a fake current date 
        $currentDateTimestamp = $this->getDateFromLastSession();
        return  DB::select("
        select u.*
        from sys_users u 
        inner join (
        select distinct s1.user_id
        from (
            select 
            s.user_id,
            min(s.id) id,
            min(s.login_tstamp) login_tstamp
            from sys_user_sessions s 
            where
            s.login_tstamp between UNIX_TIMESTAMP(DATE_SUB(STR_TO_DATE('2021-12-24', '%Y-%m-%d'), INTERVAL 2 MONTH) )
            AND UNIX_TIMESTAMP(STR_TO_DATE('2021-12-24', '%Y-%m-%d'))
            GROUP BY user_id, 
            year(FROM_UNIXTIME(s.login_tstamp)),
            month(FROM_UNIXTIME(s.login_tstamp)),
            day(FROM_UNIXTIME(s.login_tstamp))
            order by user_id 
        ) s1
        inner join (
            select 
            s.user_id,
            min(s.id) id,
            min(s.login_tstamp) login_tstamp
            from sys_user_sessions s 
            where
            s.login_tstamp between UNIX_TIMESTAMP(DATE_SUB(STR_TO_DATE('2021-12-24', '%Y-%m-%d'), INTERVAL 2 MONTH) )
            AND UNIX_TIMESTAMP(STR_TO_DATE('2021-12-24', '%Y-%m-%d'))
            GROUP BY user_id, 
            year(FROM_UNIXTIME(s.login_tstamp)),
            month(FROM_UNIXTIME(s.login_tstamp)),
            day(FROM_UNIXTIME(s.login_tstamp))
            order by user_id 
        ) s2 on s2.user_id = s1.user_id  
        and s2.login_tstamp between s1.login_tstamp AND (UNIX_TIMESTAMP(FROM_UNIXTIME(s1.login_tstamp) + INTERVAL 2 DAY))
        GROUP BY s1.user_id, s1.login_tstamp 
        HAVING COUNT(DISTINCT s2.login_tstamp) >= 3
        ) sessions on sessions.user_id = u.id
        ");
        
    }


    public function getDateFromLastSession(){
        return  DB::selectOne('select max(s.login_tstamp) login_tstamp
        from sys_user_sessions s
        ');
        
    }

}