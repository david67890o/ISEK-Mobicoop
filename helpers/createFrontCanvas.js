'use strict';

/*
* This file is used to create a canvas for front app & link bundle
*/

const fs = require('fs-extra');
const kuler = require('kuler');
const program = require('commander');
const path = require('path');
const to = require('await-to-js').default;

program
  .version('0.1.0')
  .option('-d, --destination  <dir>', 'Path to copy canvas to')
  .parse(process.argv);


if (!program.destination)  {
  process.stderr.write(kuler('You did not specify a path to copy canvas to .. ','orange'));
  return;
}


// This function check copy to path sent & link to bunlde
async function createCanvas () {
  // Check if specified path is a dir & exist
  let err, exists, success;
  let destination = path.resolve(program.destination);
  [err,exists] = await to(fs.ensureDir(destination));
  if(err){
    process.stderr.write(kuler('Path specified does not exists or is not a directory! \n','red'))
    console.error(err);
    return;
  }
  // Copy mobicoop files to sent path
  let pathToMobicoop = path.resolve(__dirname, '../interfaces/mobicoop');
  let pathToMobicoopBundle = path.resolve(pathToMobicoop, 'src/MobicoopBundle');
  let pathToCopiedBundle = path.resolve(destination, 'src/MobicoopBundle');
  process.stdout.write(kuler(`Copying files to ${destination}\n`,'green'));
  [err,success] = await to(fs.copy(pathToMobicoop, destination));
  if(err){
    process.stderr.write(kuler('Cannot copy to specified path!\n','red'))
    console.error(err);
    return;
  }
  process.stdout.write(kuler(`Canvas had been create in ${destination}\n`,'green'));
  // we try to deleted the copied bundle
  [err,success] = await to(fs.remove(pathToCopiedBundle));
  if(err){
    process.stderr.write(kuler('Cannot deleted the copied bundle\n','red'))
    console.error(err);
    return;
  }
  // We link bundle to new created folder
  process.stdout.write(kuler('Copied bundle deleted ...\n','green'));
  [err,success] = await to(fs.symlink(pathToMobicoopBundle,pathToCopiedBundle))
   if(err){
    process.stderr.write(kuler('Cannot create symlink bundle\n','red'))
    console.error(err);
    return;
  }
  process.stdout.write(kuler('Bundle are now symlinked 💪 ...\n','green'));
  // We add bundle to .gitignore
  [err,success] = await to(fs.appendFile(path.resolve(destination,'.gitignore'), '\nsrc/MobicoopBundle'));
  if(!err) process.stdout.write(kuler('Added bundle to .gitignore \n','green'))
  process.stdout.write(kuler('☢️ Do not forget to commit into monorepo when you edit bundle files ☣️ \n','cyan'));
}

// Run the main job
createCanvas();