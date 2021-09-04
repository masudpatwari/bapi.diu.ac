<?php

/**
 * Date : 2018-Jun-20;
 * Developer Name : Md. Mesbaul Islam || Arif Bin A. Aziz;
 * Contact : 01738120411;
 * E-mail : rony.max24@gmail.com;
 * Theme Name: Result Management System;
 * Theme URI: N/A;
 * Author: Dhaka International University;
 * Author URI: N/A;
 * Version: 1.1.0
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class M_RMS_EMP_ROLES extends Model
{
    public $timestamps = false;
    protected $table = "rms_emp_roles";
    protected $connection = 'mysql';
    protected $fillable = ['emp_id', 'roles_id'];

    /*
     * Call to assign roles ajax method
     *
     */
    public static function emp_roles()
    {
        return static::selectRaw('emp_id, role_id')
            ->orderby('emp_id');
    }


    public static function has_user_permission_array($route_name)
    {

      $permission_array = static::get_user_permission_array();

      if($permission_array === true ) return true;

      if( ! in_array($route_name, $permission_array))
      return false;

      return true;

    }


    public static function get_user_permission_array()
    {
      $assigned_ids_array = static::where('emp_id', session('user.id'))->pluck('role_id')->toArray();
      // $ids = implode(',', $assigned_ids_array);
      $role_permission_array = \App\Models\M_RMS_ROLES::whereIn('id',$assigned_ids_array)->pluck('permissions');


      $permission_array = separated_routes_array();
      foreach ($role_permission_array as $value) {

        // have full permission
        if($value == '*') return true;
        $array_data = @unserialize($value);
        if($array_data !==false)
         $permission_array = array_merge( $permission_array,  $array_data);
      }


      $personal_permission = @unserialize(\App\Models\M_WP_EMP::find(session('user.id'))->rms_permissions);

      if( is_array($personal_permission ) && count($permission_array)>0){
        foreach ($personal_permission as $action => $status) {
          if( $status == 'deny'){
            unset($permission_array[$action]);
          }
          else {
            $permission_array[$action] = $action;
          }
        }
      }

      return $permission_array;
    }
}
