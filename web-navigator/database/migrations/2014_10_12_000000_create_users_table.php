<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\UserRole;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->string('role', 20);
            $table->tinyInteger('is_default')->default('0');
            $table->tinyInteger('status')->default('1');
            $table->timestamps();
        });
        $this->defaultInsert(["Admin", "User"]);
        sleep(1);
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('role_id')->constrained('user_roles');
            $table->string('emp_id')->nullable();
            $table->string('tester_id')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('active')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('users');
    }
    private function defaultInsert($data = [])
    {
        foreach ($data as $key => $value) {
            $Obj = new UserRole();
            $Obj->role = $value;
            $Obj->is_default = '1';
            $Obj->save();
        }
    }
}