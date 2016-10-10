#######################################################################################################################
# Read Selection Data and Metadata
#######################################################################################################################
# Parameters:
#  - data.file    : character(1)  the path of the data file
#  - metadata.file: character(1)  the path of the metadata file
#######################################################################################################################
# A "selection" list: a list which contains all data and metadata matrices
#######################################################################################################################
read.selection <- function (data.file, metadata.file) {
  
  data <- read.delim(file=data.file, header=FALSE, stringsAsFactors=FALSE)
  if (nrow(data) <= 1 || ncol(data) <= 1) {
    stop("Invalid data file: it should contain at least 1 sample and 1 probe.")
  }
  metadata <- read.delim(file=metadata.file, header=TRUE, stringsAsFactors=FALSE)
  if (nrow(metadata) != (ncol(data)-1)) {
    stop("Invalid metadata file: it should contain the same number of samples as the data one.")
  }
  if (ncol(metadata) < 1) {
    stop("Invalid metadata file: no columns found.")
  }
  tmp <- data.matrix(data[-1,-1])
  rownames(tmp) <- NULL
  colnames(tmp) <- NULL
  selection <- list(
    data=list(
      expression.matrix=tmp,
      probes=data[,1][-1],
      samples=as.character(data[1,])[-1]
    ),
    metadata=metadata
  )
  class(selection) <- "selection"
  return (selection)
}

#######################################################################################################################
# Prepares a set of selections for integration
#######################################################################################################################
# Parameters:
#  - ...: multiple "selection" lists
#######################################################################################################################
# A "prepared.selections" list
#######################################################################################################################
prepare.selections <- function (...) {
  selections <- list(...)
  if (length(selections) < 1) {
    stop("You must specify at least one selection")
  }
  if (length(selections) != sum(sapply(selections, function (x) (class(x) == "selection")))) {
    stop("All specified parameters must be a \"selection\" list.")
  }
  common.probes <- Reduce(intersect, lapply(selections, function (x) (x$data$probes) ))
  if (length(common.probes) < 1) {
    stop("No common probes found between selections to integrate")
  }
  selected.probes <- lapply(selections, function (x, c) (which(x$data$probes %in% c)) , common.probes)
  expression.matrices <- lapply(selections, function (x, c) (ExpressionSet(assayData=x$data$expression.matrix[which(x$data$probes %in% c),])) , common.probes)
  all.samples <- Reduce(c, lapply(selections, function (x) (x$data$samples) ))
  metadata.matrices <- lapply(selections, function (x) (x$metadata) )
  result <- list(
    expression.matrices=expression.matrices,
    metadata.matrices=metadata.matrices,
    all.samples=all.samples,
    all.probes =common.probes,
    selected.probes=selected.probes
  )
  class(result) <- "prepared.selections"
  return (result)
}


