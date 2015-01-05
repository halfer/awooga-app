Project Awooga
==============

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/halfer/awooga-app/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/halfer/awooga-app/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/halfer/awooga-app/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/halfer/awooga-app/?branch=master) [![Build Status](https://scrutinizer-ci.com/g/halfer/awooga-app/badges/build.png?b=master)](https://scrutinizer-ci.com/g/halfer/awooga-app/build-status/master)

Introduction
------

This project, for which this repo is a prototype, is intended to be a crowd-sourced data repository
of programming materials on the web that contain problematic practices, and which are at risk of
actively teaching bad or insecure coding habits. Experience has shown that authors of such material
are rarely willing to improve their material, especially in the cases where necessary changes
would be substantial.

I noticed this problem after publishing a PHP tutorial called [I ♥ PHP](http://ilovephp.jondh.me.uk);
the more I looked for other material to send new coders to, the more issues I found. It turns out for
PHP that there are very few resources on the web I'd be happy to recommend (though it would not be
surprising to find the same for other languages also).

There are two basic outcomes that I would like to see as a result of this project. The first is
that authors experience a collective pressure from the technology community to improve or remove
material that teaches poor practices. Also, it would be good for new programmers to have a central
site they can check tutorials against, to warn them about resources that are not of high quality.

Data collection
------

The potential size of this data set means that it is not feasible for a single individual or group to
collate it by themselves. I therefore suggest the data is maintained by willing volunteers in
public Git repositories (e.g. GitHub, BitBucket etc). The URL of these repositories would then be
held in a central database and each would be periodically scanned for updates, without any
oversight from a central coordinator.

New volunteers wishing to be included in the effort could perhaps start with adding a few reports in
a public repo to show they are able to abide by some basic contributor guidelines:

* Reports are correct, succinct and readable
* If the contributor has contacted the author, they are polite and constructive
* An effort has been made not to list material already in the database
* Repos are free of large/redundant files that would cause disk space issues when they are cloned

Of course, a contributor who has been made active can be removed if they fail to abide by the
guidelines.

Data format
-------

Data is to be held in JSON, one file per report. The repo would be scanned recursively and any
files found with a `.json` extension are assumed to be a report. Anything that passes validation
will be inserted (or updated) in the central database, and anything that fails validation will
be listed on a public validation report (which can be consulted to aid repair).

Resources can have a title associated with them, which will be plain text. Resources can have a
one or more issue codes associated with them, selected from a list of valid codes. Both resources
and issues can have descriptions attached to them, both of which will be in markdown format.

URLs can be either strings (the resource has one URL) or an array of strings (handy if a resource
has duplicates, or is split over an article and a video).

Since the format may change over time, each JSON file will contain a format version number, to
allow for future expansion.

Other than the extension, there is no proposed standard for report filenames or directories - this
is at the whim of the author. I've labelled mine thus (where the date is when the report was added
to the repo):

	/reports/yyyy-mm-dd-title.json

Files that are renamed or moved within the repo should be detected as containing the same report,
since they will be indexed by URL and contributor ID.

Generation functions
-------

The prototype [report repo](https://github.com/halfer/awooga-reports) contains an example of how
data can be added and maintained using simple PHP scripts committed to the same repo. At a later
time, I'd like to add a simple web form that will help generate this, if there's demand.

Website
-------

This prototype website offers these features:

* Read repositories every few hours to update data
* Read data into a relational database
* Offer a browsable, paginated and searchable list of resources. Beginners and contributors alike
can search by domain or URL to see if a tutorial they have found is listed
* Offer a public list of source repo URLs and associated statistics

These are not yet added, but can be if there is demand:

* Build a downloadable JSON document for the full database, for anyone to use as they wish
* Resources could do with tagging by language or technology, for searchability

Copyright
------

All data contributed to the system would fall under some sort of copyleft license, perhaps
Creative Commons. Whilst the associated website should be fine for most purposes, anyone wanting
to do something novel with the data would be free to do so without needing to seek permission
(either from the project or from individual contributors).

Project name
------

[Awooga](https://en.wikipedia.org/wiki/Awooga) is the onomatopoeiac form of a klaxon sounding, in
English. It's slightly cartoonish, but of course it has a serious purpose: a warning.

Request for comments
------

Comments are welcome on any aspect of this proposal. Would people create reports? Are public
repos the best way to garner involvement? (using automatic pulls and not relying on manual,
centralised merges ought to streamline contributions).

Whilst my focus is PHP, reports for all popular programming languages would be welcome, as long as
claims can easily be verified.
