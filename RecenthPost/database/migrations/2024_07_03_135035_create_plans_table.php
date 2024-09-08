<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlansTable extends Migration
{
    public function up()
    {
        Schema::create("plans", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->decimal("price", 8, 2);
            $table->enum("duration", [
                "24-hours",
                "3-days",
                "7-days",
                "14-days",
                "30-days",
                "60-days",
                "90-days",
                "180-days",
                "365-days",
            ]);
            $table->string("package_type"); // Basic, Essential, Pro, etc.
            $table->integer("countries"); // Number of countries selectable
            $table->decimal("monthly_cost", 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists("plans");
    }
}
