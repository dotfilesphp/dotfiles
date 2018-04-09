#!/usr/bin/env bash

WORK_DIR=.subsplit
TAG=`cat VERSION`
SOURCE=git@github.com:kilip/dotfiles.git

echo "$TAG"
mkdir -p $WORK_DIR
cd $WORK_DIR
git subsplit init $SOURCE
git subsplit update $SOURCE

git subsplit publish --heads="master" --tags="$TAG" src/DartDigital/ReservationBundle:git@bitbucket.org:dart-digital/reservation-bundle.git
git subsplit publish --heads="master" --tags="$TAG" src/DartDigital/PaymentBundle:git@bitbucket.org:dart-digital/payment-bundle.git
git subsplit publish --heads="master" --tags="$TAG" src/DartDigital/SalesForceBundle:git@bitbucket.org:dart-digital/salesforce-bundle.git
git subsplit publish --heads="master" --tags="$TAG" src/DartDigital/HotelBundle:git@bitbucket.org:dart-digital/hotel-bundle.git
git subsplit publish --heads="master" --tags="$TAG" src/DartDigital/UserBundle:git@bitbucket.org:dart-digital/user-bundle.git
git subsplit publish --heads="master" --tags="$TAG" src/DartDigital/FlightBundle:git@bitbucket.org:dart-digital/flight-bundle.git
