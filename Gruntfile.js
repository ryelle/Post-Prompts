/* jshint node:true */
module.exports = function(grunt) {

	// Load tasks.
	require('matchdep').filterDev('grunt-*').forEach( grunt.loadNpmTasks );

	// Project configuration.
	grunt.initConfig({

		makepot: {
			pot: {
				options: {
					cwd: '.',
					domainPath: '/languages',
					mainFile: 'post-prompts.php',
					potFilename: 'kd_prompts.pot',
					type: 'wp-plugin',
					updateTimestamp: false
				}
			}
		}

	});

	// Default task.
	grunt.registerTask('default', ['makepot:pot']);

};
